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
        $current_appmode = Appmode::where('active', true)->first()->option;

        return view('adminpanel', ['activators' => $activators, 'eventcalls' => $eventcalls, 'modes' => $modes,  'current_appmode' => $current_appmode]);
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
            return redirect()->route('adminpanel')->with('danger', db4scw_validatorerrors($validator));
        }

        //get validated fields
        $attributes = $validator->validated();
        $value = $attributes['mode'];

        //get past and future appmode
        $current_appmode = Appmode::where('active', true)->first();
        $future_appmode = Appmode::where('option', $value)->first();

        //return early if no change is necessary
        if($current_appmode->id == $future_appmode->id)
        {
            //redirect back to adminpanel
            return redirect()->route('adminpanel')->with('success', 'No change in coordinatorr mode necessary.');
        }

        //switch appmodes
        $current_appmode->active = false;
        $current_appmode->save();
        $future_appmode->active = true;
        $future_appmode->save();

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
