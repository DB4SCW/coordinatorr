<?php

namespace App\Http\Controllers;

use App\Models\Callsign;
use App\Models\Appmode;
use App\Models\Band;
use App\Models\Mode;
use Illuminate\Http\Request;
use Spatie\DiscordAlerts\Facades\DiscordAlert;

class HamalertController extends Controller
{
    public function receive()
    {
        //get appmode
        $appmode = env('COORDINATORR_MODE', 'SINGLEOP');

        //check if Appmode is valid
        if (Appmode::where('option', $appmode)->count() < 1) {
            return response()->json(['message' => 'The mode of this coordinatorr instance is not configured well.'], 500);
        }

        //get json from request
        $data = request()->json()->all();

        //get callsign from spot
        $spot_callsign = db4scw_getcallsignwithoutadditionalinfo($data['callsign']);

        //get callsign from database
        $callsign = Callsign::where('call', $spot_callsign)->first();

        //return callsign not found
        if ($callsign == null) {
            return response()->json(['message' => 'Callsign ' . $spot_callsign . ' not found in this instance.'], 404);
        }

        //extract data from hamalert spot
        $spotter = (string)$data['spotter'] ?? '';
        $comment = (string)$data['comment'] ?? '';
        $band = (string)$data['band'];
        $frequency = (float)$data['frequency'];
        $mode = strtoupper((string)$data['modeDetail']);
        $time = \Carbon\Carbon::createFromFormat('Y-m-d H:i', \Carbon\Carbon::now()->format('Y-m-d') . ' ' . $data['time']);

        //get coordinatorr-mode
        switch ($mode) {
            case 'CW':
                $coordinatorr_mode = "CW";
                break;
            case 'SSB':
                $coordinatorr_mode = "VOICE";
                break;
            case 'LSB':
                $coordinatorr_mode = "VOICE";
                break;
            case 'USB':
                $coordinatorr_mode = "VOICE";
                break;
            case 'FM':
                $coordinatorr_mode = "VOICE";
                break;
            case 'DIGITALVOICE':
                $coordinatorr_mode = "VOICE";
                break;
            case 'DMR':
                $coordinatorr_mode = "VOICE";
                break;
            case 'YSF':
                $coordinatorr_mode = "VOICE";
                break;
            case 'DSTAR':
                $coordinatorr_mode = "VOICE";
                break;
            default:
                $coordinatorr_mode = "DIGITAL";
                break;
        }

        //get modeid and bandid for coordinatorr
        $modeid = Mode::where('mode', $coordinatorr_mode)->first()->id;
        $bandid = Band::where('start', '<=', $frequency)->where('end', '>=', $frequency)->first()->id;

        //check if callsign has active activation
        $current_activation = $callsign->activations()->where('end', null);

        //add constrictions based on appmode
        $current_activation = db4scw_add_mode_constrictions($current_activation, $appmode, $bandid, $modeid);

        //get activation from database
        $current_activation = $current_activation->first();
        $APPNAME = env('APP_NAME');
        $URL = env('APP_URL');
        $avatar = env('DISCORD_AVATAR');
        $username = env('DISCORD_USERNAME');

        //return "Accepted" when callsign is fine, but no active activation is present
        if ($current_activation == null) {
            // Check if DISCORD_ENABLED is set to true
            if (env('DISCORD_ENABLED', false)) {
                DiscordAlert::message(":warning: {$spot_callsign} coordination conflict: No operator found. Please resolve on: {$URL}", [
                    [
                        'title' => "{$spot_callsign} spotted",
                        'description' => "No operator found for {$spot_callsign}.",
                        'color' => '#E77625',
                        'fields' => [
                            [
                                'name' => 'Band / Frequency',
                                'value' => $band . ' / ' . $frequency . 'MHz',
                                'inline' => true
                            ],
                            [
                                'name' => 'Mode',
                                'value' => $mode,
                                'inline' => true
                            ],
                            [
                                'name' => 'Comment',
                                'value' => $comment,
                            ],
                            [
                                'name' => 'Time',
                                'value' => '<t:' . time() . ':R>',
                            ]
                        ],
                        'author' => [
                            'name' => $APPNAME,
                            'url' => $URL,
                        ],
                        'footer' => [
                            'text' => 'Spot received from HamAlert'
                        ],
                        'username' => $username,
                        'avatar_url' => $avatar

                    ]
                ]);
            }
            return response()->json(['message' => 'Callsign ' . $spot_callsign . ' has no current activation.'], 202);
        }

        //set new info for activation
        $current_activation->hamalert_spotter = $spotter;
        $current_activation->hamalert_frequency = $frequency;
        $current_activation->hamalert_mode = $mode;
        $current_activation->hamalert_spot_datetime = $time;
        $current_activation->hamalert_spot_count = ($current_activation->hamalert_spot_count ?? 0) + 1;
        $current_activation->save();
        $activator_callsign = $current_activation->activator->call;

        // Check if DISCORD_ENABLED is set to true
        if (env('DISCORD_ENABLED', false)) {
            DiscordAlert::message(":white_check_mark: {$spot_callsign} spotted", [
                [
                    'title' => "{$spot_callsign} spotted",
                    'description' => "{$activator_callsign} has been spotted as {$spot_callsign}.",
                    'color' => '#00FF00',
                    'fields' => [
                        [
                            'name' => 'Band / Frequency',
                            'value' => $band . ' / ' . $frequency . 'MHz',
                            'inline' => true
                        ],
                        [
                            'name' => 'Mode',
                            'value' => $mode,
                            'inline' => true
                        ],
                        [
                            'name' => 'Comment',
                            'value' => $comment,
                        ],
                        [
                            'name' => 'Time',
                            'value' => '<t:' . time() . ':R>',
                        ]
                    ],
                    'author' => [
                        'name' => $APPNAME,
                        'url' => $URL,
                    ],
                    'footer' => [
                        'text' => 'Spot received from HamAlert'
                    ]
                ]
            ]);
        }

        //return positive resopnse
        return response()->json(['message' => 'New info for callsign ' . $spot_callsign . ' has been set.'], 200);
    }
}
