<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
