<?php

namespace App\Http\Controllers;

use App\Models\Activation;
use App\Models\Activator;
use App\Models\Callsign;
use App\Models\Appmode;
use App\Models\PlannedActivation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class AdminpanelController extends Controller
{
    public function index()
    {
             
        $activators = Activator::orderBy('call', 'ASC')->get();
        $eventcalls = Callsign::orderBy('call', 'ASC')->get();
        $modes = Appmode::all();

        return view('adminpanel', ['activators' => $activators, 'eventcalls' => $eventcalls, 'modes' => $modes ]);
    }

    public function switchmode()
    {
        //validate input
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), [
            'mode' => 'required|exists:appmodes,option'
        ], 
        [
            'mode.exists' => 'This mode does not exist.'
        ]);

        //handle failure of validation
        if ($validator->fails()) {
            return redirect()->route('adminpanel')->with('danger', skd_validatorerrors($validator));
        }

        //get validated fields
        $attributes = $validator->validated();
        $value = $attributes['mode'];

        //define env key
        $key = "COORDINATORR_MODE";

        //get environment file
        $envFile = app()->environmentFilePath();
        $envcontent = file_get_contents($envFile);

        //check if key exists
        $keyPosition = strpos($envcontent, "{$key}=");

        // If key exists, replace it. Otherwise, add the new key-value pair.
        if ($keyPosition !== false) {
            $oldline = $key . "=" . env($key);
            $envcontent = str_replace($oldline, "{$key}={$value}", $envcontent);
        } else {
            $envcontent .= "\n{$key}={$value}";
        }

        //write new env file
        try {
            file_put_contents($envFile, $envcontent);
        } catch (\Throwable $th) {
            return redirect()->route('adminpanel')->with('danger', 'Failed to change coordinatorr mode.');
        }

        //purge all current activations
        $current_activations = Activation::where('end', null)->get();
        $current_activations->each(function ($activation) {
            $activation->delete();
        });

        //purge all upcoming planned activations
        $upcoming_planned_activations = PlannedActivation::all()->where('end', '>', \Carbon\Carbon::now());
        $upcoming_planned_activations->each(function ($planned_activation) {
            $planned_activation->delete();
        });
        
        //redirect back to adminpanel
        return redirect()->route('adminpanel')->with('success', 'Successfully changed coordinatorr mode.');
    }
}
