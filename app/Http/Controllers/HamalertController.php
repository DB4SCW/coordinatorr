<?php

namespace App\Http\Controllers;

use App\Models\Callsign;
use Illuminate\Http\Request;

class HamalertController extends Controller
{
    public function receive()
    {
        //get json from request
        $data = request()->json()->all();

        //get callsign from spot
        $spot_callsign = swolf_getcallsignwithoutadditionalinfo($data['fullCallsign']);

        //get callsign from database
        $callsign = Callsign::where('call', $spot_callsign)->first();

        //return callsign not found
        if($callsign == null)
        {
            return response()->json(['message' => 'Callsign ' . $spot_callsign . ' not found in this instance.'], 404); 
        }

        //check if callsign has active activation
        $current_activation = $callsign->activations()->where('end', null)->first();

        //return "Accepted" when callsign is fine, but no active activation is present
        if($current_activation == null)
        {
            return response()->json(['message' => 'Callsign ' . $spot_callsign . ' has no current activation.'], 202); 
        }

        //extract data from hamalert spot
        $spotter = (string)$data['spotter'] ?? '';
        $frequency = (float)$data['frequency'];
        $mode = strtoupper((string)$data['modeDetail']);
        $time = \Carbon\Carbon::createFromFormat('Y-m-d H:i', \Carbon\Carbon::now()->format('Y-m-d') . ' ' . $data['time']);

        //set new info for activation
        $current_activation->hamalert_spotter = $spotter;
        $current_activation->hamalert_frequency = $frequency;
        $current_activation->hamalert_mode = $mode;
        $current_activation->hamalert_spot_datetime = $time;
        $current_activation->hamalert_spot_count = ($current_activation->hamalert_spot_count ?? 0) + 1;
        $current_activation->save();

        //return positive resopnse
        return response()->json(['message' => 'New info for callsign ' . $spot_callsign . ' has been set.'], 200); 
    }
}
