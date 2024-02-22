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
}
