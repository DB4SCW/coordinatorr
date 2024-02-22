<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlannedActivation;
use App\Models\Callsign;
use App\Models\Activator;

class PlannedActivationController extends Controller
{
    public function index()
    {
        //get infos from database
        $callsigns = Callsign::where('hidden', false)->get();
        $planned_activations = PlannedActivation::orderBy('start')->with('callsign', 'activator')->get()->where('end', '>', \Carbon\Carbon::now());

        //show list
        return view('planned_activations', ['callsigns' => $callsigns, 'planned_activations' => $planned_activations]);
    }

    public function add()
    {
        //preprocess inputs
        $inputattributes = request()->all();
        $inputattributes['activator_callsign'] = strtoupper($inputattributes['activator_callsign']);
        
        //Input validieren
        $validator = \Illuminate\Support\Facades\Validator::make($inputattributes, [
            'eventcallsignid' => 'exists:callsigns,id',
            'activator_callsign' => 'required|exists:activators,call',
            'start' => 'required|date|after:now',
            'end' => 'required|date|after:start'
        ], 
        [
            'eventcallsignid.exists' => 'This callsign does not exist.',
            'activator_callsign.exists' => 'This activator callsign is not recognised.',
            'activator_callsign.required' => 'The activator callsign must be entered.',
            'start.required' => 'Start Date and Time has to be filled.',
            'start.date' => 'Start Date has to be a valid date.',
            'start.after' => 'Start Date has to be in the future.',
            'end.required' => 'End Date and Time has to be filled.',
            'end.date' => 'End Date has to be a valid date.',
            'end.after' => 'End Date has to be after the start date.'
        ]);

        //Validierungsfail behandeln
        if ($validator->fails()) {
            return redirect()->route('planned_activations')->with('danger', skd_validatorerrors($validator))->withInput();
        }

        //validierte Felder abholen
        $attributes = $validator->validated();

        //load and extract infos
        $activator = Activator::where('call', $attributes['activator_callsign'])->first();
        $callsign = Callsign::find($attributes['eventcallsignid']);
        $start = \Carbon\Carbon::parse($attributes['start']);
        $end = \Carbon\Carbon::parse($attributes['end']);

        //check if activator is locked or callsign is hidden
        if($activator->locked or $callsign->hidden)
        {
            return redirect()->route('planned_activations')->with('danger', 'Invalid data given.')->withInput();
        }

        //get concurrent planned activations
        if($callsign->plannedactivations->where('end', '>=', $start)->where('start', '<=', $end)->count() > 0)
        {
            return redirect()->route('planned_activations')->with('danger', 'There is already another activation of this call planned during this time.')->withInput();
        }

        //create new planned activation
        $activation = new PlannedActivation();
        $activation->callsign_id = $callsign->id;
        $activation->activator_id = $activator->id;
        $activation->start = $start;
        $activation->end = $end;
        $activation->save();

        //redirect back to list
        return redirect()->route('planned_activations')->with('success', 'Activation planned successfully.');

    }

    public function remote(PlannedActivation $plannedactivation)
    {
        //delete planned activation
        $plannedactivation->delete();
        
        //redirect back to list
        return redirect()->route('planned_activations')->with('success', 'Activation deleted successfully.');
    }
}
