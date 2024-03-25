<?php

namespace App\Http\Controllers;

use App\Models\Callsign;
use Illuminate\Http\Request;

class CallsignController extends Controller
{
    public function status(Callsign $callsign)
    {
        //check if callsign is currently active
        $current = $callsign->activations()->where('end', null)->first();

        if($current != null)
        {
            return view('status', ['message' => $callsign->call . " is currently on the air. Check the clusters!"]);
        }

        //check if there are planned activations
        $planned = $callsign->plannedactivations()->orderBy('start', 'desc')->first();

        if($planned != null)
        {
            return view('status', ['message' => "Next activation: " . $planned->start->setTimezone('UTC') . " UTC"]);
        }

        //return no result
        return view('status', ['message' => "There are currently no activations planned."]);
    }

    public function destroy(Callsign $callsign)
    {
        //delete related data
        foreach ($callsign->activations as $activations) {
            $activations->delete();
        }

        foreach ($callsign->plannedactivations as $plannedactivation) {
            $plannedactivation->delete();
        }

        //delete callsign
        $callsign->delete();

        //return back
        return redirect()->route('adminpanel')->with('success', 'Event Callsign and all related data deleted successfully.');
    }

    public function create()
    {
        //preprocess inputs
        $inputattributes = request()->all();
        $inputattributes['event_callsign'] = strtoupper($inputattributes['event_callsign']);
        $inputattributes['event_callsign'] = str_replace(' ', '', $inputattributes['event_callsign']);
        
        //Input validieren
        $validator = \Illuminate\Support\Facades\Validator::make($inputattributes, [
            'event_callsign' => 'required|unique:callsigns,call|max:10'
        ], 
        [
            'event_callsign.unique' => 'This callsign already exists.',
            'event_callsign.required' => 'Event callsign is required.',
            'event_callsign.max' => 'Event callsign may not be longer than 10 characters.'
        ]);

        //Validierungsfail behandeln
        if ($validator->fails()) {
            return redirect()->route('adminpanel')->with('danger', skd_validatorerrors($validator))->withInput();
        }

        //validierte Felder abholen
        $attributes = $validator->validated();

        //create new activator
        $activator = new Callsign();
        $activator->call = $attributes['event_callsign'];
        $activator->save();

        //return back
        return redirect()->route('adminpanel')->with('success', 'Callsign successfully created.');
    }
}
