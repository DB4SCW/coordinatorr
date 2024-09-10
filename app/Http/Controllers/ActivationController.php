<?php

namespace App\Http\Controllers;

use App\Models\Activation;
use App\Models\Callsign;
use App\Models\Activator;
use App\Models\Appmode;
use App\Models\Band;
use App\Models\Mode;
use Illuminate\Http\Request;

class ActivationController extends Controller
{
    public function index()
    {
        //get infos from database
        $callsigns = Callsign::where('hidden', false)->get();
        $current_activations = Activation::where('end', null)->with('callsign', 'activator')->get();
        $appmode = env('COORDINATORR_MODE', 'SINGLEOP');
        $bands = Band::all();
        $modes = Mode::all();

        //show list
        return view('dashboard', ['callsigns' => $callsigns, 'current_activations' => $current_activations, 'appmode' => $appmode, 'bands' => $bands, 'modes' => $modes]);
    }


    public function add()
    {
        //get appmode
        $appmode = env('COORDINATORR_MODE', 'SINGLEOP');

        //check if Appmode is valid
        if(Appmode::where('option', $appmode)->count() < 1) {
            return redirect()->route('home')->with('danger', 'Coordinator mode is invalid.');
        }
        
        //preprocess inputs
        $inputattributes = request()->all();
        $inputattributes['activator_callsign'] = strtoupper($inputattributes['activator_callsign']);
        $inputattributes['activator_callsign'] = str_replace(' ', '', $inputattributes['activator_callsign']);
        
        //define basic validation rules
        $validationrules = [
            'eventcallsignid' => 'exists:callsigns,id',
            'activator_callsign' => 'required|exists:activators,call',
            'band_id' => 'nullable',
            'mode_id'=> 'nullable'
        ];

        //Define basic error messages for validation
        $errormessages = [
            'eventcallsignid.exists' => 'This callsign does not exist.',
            'activator_callsign.exists' => 'This activator callsign is not recognised.',
            'activator_callsign.required' => 'The activator callsign must be entered.'
        ];

        //add new validation rules and error messages depending on app mode
        if($appmode == "MULTIOPBAND" or $appmode == "MULTIOPMODE")
        {
            $validationrules['band_id'] = 'exists:bands,id';
            $errormessages['band_id.exists'] = 'This band does not exist.';
        }

        if($appmode == "MULTIOPMODE")
        {
            $validationrules['mode_id'] = 'exists:modes,id';
            $errormessages['mode_id.exists'] = 'This mode does not exist.';
        }

        //Input validieren
        $validator = \Illuminate\Support\Facades\Validator::make($inputattributes, $validationrules, $errormessages);

        //Validierungsfail behandeln
        if ($validator->fails()) {
            return redirect()->route('home')->with('danger', skd_validatorerrors($validator));
        }

        //validierte Felder abholen
        $attributes = $validator->validated();

        //get infos
        $activator = Activator::where('call', $attributes['activator_callsign'])->first();
        $callsign = Callsign::find($attributes['eventcallsignid']);
        $bandid = $attributes['band_id'];
        $modeid = $attributes['mode_id'];

        //check if activator is locked or callsign is hidden
        if($activator->locked or $callsign->hidden)
        {
            return redirect()->route('home')->with('danger', 'Invalid data given.');
        }

        //construct query if there is a activation for this callsign running
        $concurrent_activation_callsign = Activation::where([['callsign_id', $callsign->id], ['end', null]]);
        
        //add constrictions based on appmode
        $concurrent_activation_callsign = db4scw_add_mode_constrictions($concurrent_activation_callsign, $appmode, $bandid, $modeid);
        
        //get result from database
        $concurrent_activation_callsign = $concurrent_activation_callsign->get();

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

        //add constrictions based on appmode
        $reservations = db4scw_add_mode_constrictions($reservations, $appmode, $bandid, $modeid);
        
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
        $activation->band_id = $bandid;
        $activation->mode_id = $modeid;
        $activation->save();

        //check if there are other activations up to 4 hours in the future where this activator is not the reserving activator
        $future_reservations = $callsign->plannedactivations()->where('activator_id', '!=', $activator->id)->where('end', '>=', \Carbon\Carbon::now())->orderBy('start');
        
        //add constrictions based on appmode
        $future_reservations = db4scw_add_mode_constrictions($future_reservations, $appmode, $bandid, $modeid);

        //check if there are other activations up to 4 hours in the future where this activator is not the reserving activator
        $future_reservations->get()->where('start', '<=', \Carbon\Carbon::now()->addHours(env("COORDINATORR_CHECK_RESERVATIONS_IN_ADVANCE_HOURS", 4)));

        //decide which on-screen-message to display
        if($future_reservations->count() > 0)
        {
            //redirect back to list
            $nextactivation = $future_reservations->first();
            return redirect()->route('home')->with('warning', 'Another activation by ' . $nextactivation->activator->call . ' starts in ' . $nextactivation->start->diff(\Carbon\Carbon::now())->format('%H:%I') . ' h. Please check!');
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
