<?php

namespace App\Http\Controllers;

use App\Models\Activation;
use App\Models\Callsign;
use App\Models\Activator;
use Illuminate\Http\Request;

class ActivationController extends Controller
{
    public function index()
    {
        //get infos from database
        $callsigns = Callsign::where('hidden', false)->get();
        $current_activations = Activation::where('end', null)->with('callsign', 'activator')->get();

        //show list
        return view('dashboard', ['callsigns' => $callsigns, 'current_activations' => $current_activations]);
    }


    public function add()
    {
        //preprocess inputs
        $inputattributes = request()->all();
        $inputattributes['activator_callsign'] = strtoupper($inputattributes['activator_callsign']);
        $inputattributes['activator_callsign'] = str_replace(' ', '', $inputattributes['activator_callsign']);
        
        //Input validieren
        $validator = \Illuminate\Support\Facades\Validator::make($inputattributes, [
            'eventcallsignid' => 'exists:callsigns,id',
            'activator_callsign' => 'required|exists:activators,call'
        ], 
        [
            'eventcallsignid.exists' => 'This callsign does not exist.',
            'activator_callsign.exists' => 'This activator callsign is not recognised.',
            'activator_callsign.required' => 'The activator callsign must be entered.'
        ]);

        //Validierungsfail behandeln
        if ($validator->fails()) {
            return redirect()->route('home')->with('danger', skd_validatorerrors($validator));
        }

        //validierte Felder abholen
        $attributes = $validator->validated();

        //get infos
        $activator = Activator::where('call', $attributes['activator_callsign'])->first();
        $callsign = Callsign::find($attributes['eventcallsignid']);

        //check if activator is locked or callsign is hidden
        if($activator->locked or $callsign->hidden)
        {
            return redirect()->route('home')->with('danger', 'Invalid data given.');
        }

        //Check if there is a activation for this callsign running
        $concurrent_activation_callsign = Activation::where([['callsign_id', $callsign->id], ['end', null]])->get();

        if($concurrent_activation_callsign->count() > 0)
        {
            return redirect()->route('home')->with('danger', 'Callsign ' . $callsign->call . ' currently active with activator ' . $concurrent_activation_callsign[0]->activator->call . '.');
        }

        //Check if there is a activation for this activator running
        $concurrent_activation_activator = Activation::where([['activator_id', $activator->id], ['end', null]])->get();

        if($concurrent_activation_activator->count() > 0)
        {
            return redirect()->route('home')->with('danger', 'Activator ' . $activator->call . ' is currently active with call ' . $concurrent_activation_activator[0]->callsign->call . '.');
        }

        //Check if there are planned activations for that callsign and check, if activator is in this
        $reservations = $callsign->plannedactivations->where('start', '<=', \Carbon\Carbon::now())->where('end', '>=', \Carbon\Carbon::now());
        
        if($reservations->count() > 0)
        {
            if(!in_array($activator->id, $reservations->pluck('activator_id')->toArray()))
            {
                return redirect()->route('home')->with('danger', 'This callsign is already reserved for another operator at this time.');
            }
        }

        //create new activation
        $activation = new Activation();
        $activation->callsign_id = $callsign->id;
        $activation->activator_id = $activator->id;
        $activation->start = \Carbon\Carbon::now();
        $activation->save();

        $future_reservations = $callsign->plannedactivations()->orderBy('start')->get()->where('start', '<=', \Carbon\Carbon::now()->addHours(4));

        if($future_reservations->count() > 0)
        {
            //redirect back to list
            return redirect()->route('home')->with('warning', 'Another activation starts in ' . $future_reservations->first()->start->diff(\Carbon\Carbon::now())->format('%H:%I:%S') . '. Please check!');
        }else
        {
            //redirect back to list
            return redirect()->route('home')->with('success', 'Activation started successfully.');
        }
        
    }

    public function end(Activation $activation)
    {
        
        //check if activation can be ended
        if($activation->end != null)
        {
            return redirect()->route('home')->with('danger', 'This activation has already ended.');
        }
        
        //set end of activation and save
        $activation->end = \Carbon\Carbon::now();
        $activation->save();

        //redirect back to list
        return redirect()->route('home')->with('success', 'Activation ended successfully.');

    }
}
