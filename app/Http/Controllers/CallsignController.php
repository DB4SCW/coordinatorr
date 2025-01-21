<?php

namespace App\Http\Controllers;

use App\Models\Callsign;
use Illuminate\Http\Request;

class CallsignController extends Controller
{
    public function status(Callsign $callsign)
    {
        //check if callsign is currently active
        $current_activations = $callsign->activations()->where('end', null)->orderBy('start', 'desc');

        //display current information
        if($current_activations->count() > 0)
        {
            //sort by hamalert data, get hamalert enabled activations first
            $current_activations_sorted = $current_activations->get()->sortByDesc(function ($item) {
                return !is_null($item->hamalert_spot_datetime);
            });
            
            //get first activation
            $current = $current_activations_sorted->first();

            //display hamalert spot data if available
            if($current->hamalert_spot_datetime != null)
            {
                return view('status', ['message' => $callsign->call . " is QRV @ " . $current->hamalert_frequency . " MHz " . $current->hamalert_mode]);
            }else
            {
                return view('status', ['message' => $callsign->call . " is currently on the air. Check the clusters!"]);
            }            
        }

        //check if there are planned activations
        $planned = $callsign->plannedactivations()->where('end', '>', \Carbon\Carbon::now())->orderBy('start', 'desc')->first();

        //display planned activations
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
        
        //validate input
        $validator = \Illuminate\Support\Facades\Validator::make($inputattributes, [
            'event_callsign' => 'required|unique:callsigns,call|max:10'
        ], 
        [
            'event_callsign.unique' => 'This callsign already exists.',
            'event_callsign.required' => 'Event callsign is required.',
            'event_callsign.max' => 'Event callsign may not be longer than 10 characters.'
        ]);

        //handle fail of validation
        if ($validator->fails()) {
            return redirect()->route('adminpanel')->with('danger', skd_validatorerrors($validator))->withInput();
        }

        //get validated fields
        $attributes = $validator->validated();

        //create new activator
        $callsign = new Callsign();
        $callsign->call = $attributes['event_callsign'];
        $callsign->calendar_color = db4scw_get_new_distict_muted_color();
        $callsign->save();

        //return back
        return redirect()->route('adminpanel')->with('success', 'Callsign successfully created.');
    }

    public function hide(Callsign $callsign)
    {

        //switch lock for activator
        $callsign->hidden = !$callsign->hidden;
        $callsign->save();

        //return back
        return redirect()->route('adminpanel')->with('success', 'Callsign hiding flag switched successfully.');
    }
}
