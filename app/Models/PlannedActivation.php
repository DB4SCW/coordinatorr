<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use stdClass;

class PlannedActivation extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = ['start' => 'datetime', 'end' => 'datetime'];

    public function callsign() : HasOne
    {
        return $this->hasOne(Callsign::class, "id", "callsign_id");
    }

    public function activator() : HasOne
    {
        return $this->hasOne(Activator::class, "id", "activator_id");
    }

    public function band() : HasOne
    {
        return $this->hasOne(Band::class, 'id', 'band_id');
    }

    public function mode() : HasOne
    {
        return $this->hasOne(Mode::class, 'id', 'mode_id');
    }

    public function getcalendarformat(bool $withcallsign = true, string $appmode = "")
    {
       
        //safety check for appmode
        if($appmode == "")
        {
            $appmode = db4scw_get_current_appmode();
        }

        //construct format for Fullcalendar plugin
        $format = new stdClass();
        $format->id = $this->id;
        $format->url = "/planned_activations";

        //construct title
        $format->title = ( $withcallsign ? ($this->callsign->call . ": ") : '') . $this->activator->call;

        if(($appmode == "MULTIOPBAND" or $appmode == "MULTIOPMODE") and $this->band_id != null)
        {
            $format->title = $format->title . " @ " . $this->band->band;
        }

        if($appmode == "MULTIOPMODE" and $this->mode_id != null)
        {
            $format->title = $format->title . " " . $this->mode->mode;
        }

        //configure date
        $format->start = $this->start->setTimezone('UTC')->format('Y-m-d\TH:i:s');
        $format->end = $this->end->setTimezone('UTC')->format('Y-m-d\TH:i:s');
        
        //get configured color, if we have more than 1 callsign present
        if($withcallsign and $this->callsign->calendar_color != null)
        {
            $format->color = $this->callsign->calendar_color;
        }
        
        //return correct format as stdClass
        return $format;
    
    }
}
