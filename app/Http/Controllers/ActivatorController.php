<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activator;

class ActivatorController extends Controller
{
    public function destroy(Activator $activator)
    {
        //delete related data
        foreach ($activator->activations as $activations) {
            $activations->delete();
        }

        foreach ($activator->plannedactivations as $plannedactivation) {
            $plannedactivation->delete();
        }

        //delete activator
        $activator->delete();

        //return back
        return redirect()->route('adminpanel')->with('success', 'Activator and all related data deleted successfully.');
    }

    public function create()
    {
        //preprocess inputs
        $inputattributes = request()->all();
        $inputattributes['activator_callsign'] = strtoupper($inputattributes['activator_callsign']);
        $inputattributes['activator_callsign'] = str_replace(' ', '', $inputattributes['activator_callsign']);
        
        //validate input
        $validator = \Illuminate\Support\Facades\Validator::make($inputattributes, [
            'activator_callsign' => 'required|unique:activators,call|max:10'
        ], 
        [
            'activator_callsign.unique' => 'This callsign already exist.',
            'activator_callsign.required' => 'Activator callsign is required.',
            'activator_callsign.max' => 'Activator callsign may not be longer than 10 characters.'
        ]);

        //handle fail of validation
        if ($validator->fails()) {
            return redirect()->route('adminpanel')->with('danger', db4scw_validatorerrors($validator))->withInput();
        }

        //get validated fields
        $attributes = $validator->validated();

        //create new activator
        $activator = new Activator();
        $activator->call = $attributes['activator_callsign'];
        $activator->save();

        //return back
        return redirect()->route('adminpanel')->with('success', 'Activator successfully created.');
    }

    public function lock(Activator $activator)
    {

        //switch lock for activator
        $activator->locked = !$activator->locked;
        $activator->save();

        //return back
        return redirect()->route('adminpanel')->with('success', 'Activator lock switched successfully.');
    }
}
