<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use \App\Models\Activator;

class Activation extends Model
{
    use HasFactory;

    protected $casts = ['start' => 'datetime', 'end' => 'datetime', 'hamalert_spot_datetime' => 'datetime'];

    public function callsign() : HasOne
    {
        return $this->hasOne(Callsign::class, "id", "callsign_id");
    }

    public function activator() : HasOne
    {
        return $this->hasOne(Activator::class, "id", "activator_id");
    }
}
