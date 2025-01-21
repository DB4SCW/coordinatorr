<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlannedActivation;
use App\Models\Callsign;
use App\Models\Activator;
use App\Models\Appmode;
use App\Models\Band;
use App\Models\Mode;

class PlannedActivationController extends Controller
{
    public function index()
    {
        //get infos from database
        $callsigns = Callsign::where('hidden', false)->get();
        $planned_activations = PlannedActivation::orderBy('start')->with('callsign', 'activator')->get()->where('end', '>', \Carbon\Carbon::now());
        $appmode = env('COORDINATORR_MODE', 'SINGLEOP');
        $bands = Band::all();
        $modes = Mode::all();

        //show list
        return view('planned_activations', ['callsigns' => $callsigns, 'planned_activations' => $planned_activations, 'appmode' => $appmode, 'bands' => $bands, 'modes' => $modes]);
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

        //define basic validation rules
        $validationrules = [
            'eventcallsignid' => 'exists:callsigns,id',
            'activator_callsign' => 'required|exists:activators,call',
            'start' => 'required|date|after:now',
            'end' => 'required|date|after:start', 
            'band_id' => 'nullable',
            'mode_id' => 'nullable'
        ];

        //Define basic error messages for validation
        $errormessages = [
            'eventcallsignid.exists' => 'This callsign does not exist.',
            'activator_callsign.exists' => 'This activator callsign is not recognised.',
            'activator_callsign.required' => 'The activator callsign must be entered.',
            'start.required' => 'Start Date and Time has to be filled.',
            'start.date' => 'Start Date has to be a valid date.',
            'start.after' => 'Start Date has to be in the future.',
            'end.required' => 'End Date and Time has to be filled.',
            'end.date' => 'End Date has to be a valid date.',
            'end.after' => 'End Date has to be after the start date.'
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
        
        //validate input
        $validator = \Illuminate\Support\Facades\Validator::make($inputattributes, $validationrules, $errormessages);

        //handle fail of validation
        if ($validator->fails()) {
            return redirect()->route('planned_activations')->with('danger', skd_validatorerrors($validator))->withInput();
        }

        //get validated fields
        $attributes = $validator->validated();

        //load and extract infos
        $activator = Activator::where('call', $attributes['activator_callsign'])->first();
        $callsign = Callsign::find($attributes['eventcallsignid']);
        $start = \Carbon\Carbon::parse($attributes['start']);
        $end = \Carbon\Carbon::parse($attributes['end']);
        $bandid = $attributes['band_id'];
        $modeid = $attributes['mode_id'];

        //check if activator is locked or callsign is hidden
        if($activator->locked or $callsign->hidden)
        {
            return redirect()->route('planned_activations')->with('danger', 'Invalid data given.')->withInput();
        }

        //get concurrent planned activations
        $concurrent_planned_activations = $callsign->plannedactivations()->where('end', '>=', $start)->where('start', '<=', $end);

        //add constrictions based on appmode
        $concurrent_planned_activations = db4scw_add_mode_constrictions($concurrent_planned_activations, $appmode, $bandid, $modeid);
        
        //abort if there is another concurrent activation
        if($concurrent_planned_activations->count() > 0)
        {
            return redirect()->route('planned_activations')->with('danger', 'There is already another activation of this call planned during this time.')->withInput();
        }

        //create new planned activation
        $activation = new PlannedActivation();
        $activation->callsign_id = $callsign->id;
        $activation->activator_id = $activator->id;
        $activation->start = $start;
        $activation->end = $end;
        $activation->band_id = $bandid;
        $activation->mode_id = $modeid;
        $activation->save();

        //redirect back to list
        return redirect()->route('planned_activations')->with('success', 'Activation planned successfully.');

    }

    public function remove(PlannedActivation $plannedactivation)
    {
        //delete planned activation
        $plannedactivation->delete();
        
        //redirect back to list
        return redirect()->route('planned_activations')->with('success', 'Activation deleted successfully.');
    }

    public function export_for_calendar()
    {

        //get request
        $arguments = request()->all();
        
        //get all planned activations
        $planned_activations = PlannedActivation::orderBy('start')->with('callsign', 'activator');

        //parse request data and narrow down data loading
        if(array_key_exists('start', $arguments) and array_key_exists('end', $arguments))
        {
            $start = \Carbon\Carbon::parse( substr($arguments['start'], 0, 10));
            $end = \Carbon\Carbon::parse( substr($arguments['end'], 0, 10));
            $planned_activations = $planned_activations->where('start', '>=', $start);
            $planned_activations = $planned_activations->where('end', '<=', $end);
        }

        //get data from database
        $planned_activations = $planned_activations->where('end', '>', \Carbon\Carbon::now())->get();

        //check if we have more than 1 callsign possible are actual
        $possiblecalls = Callsign::where('hidden', false)->count();

        //decide if the callsign should be exported as the calendar title
        $withcallsign = false;

        if($possiblecalls != 1)
        {
            $withcallsign = true;
        }

        //check if there are more than 1 call in the existing planned activations
        if($planned_activations->pluck('callsign.call')->unique()->count() != 1)
        {
            $withcallsign = true;
        }

        //get calendar format for FullCalendar
        $calendarData = $planned_activations->map(function ($planned_activation) use($withcallsign) {
            return $planned_activation->getcalendarformat($withcallsign);
        });

        //get json data and return 
        return response()->json(array_values($calendarData->toArray()));
    }

    public function showcalendar()
    {
        return view('calendar', []);
    }
}
