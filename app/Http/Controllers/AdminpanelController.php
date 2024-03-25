<?php

namespace App\Http\Controllers;

use App\Models\Activator;
use App\Models\Callsign;
use Illuminate\Http\Request;

class AdminpanelController extends Controller
{
    public function index()
    {
        $activators = Activator::orderBy('call', 'ASC')->get();
        $eventcalls = Callsign::orderBy('call', 'ASC')->get();

        return view('adminpanel', ['activators' => $activators, 'eventcalls' => $eventcalls]);
    }
}
