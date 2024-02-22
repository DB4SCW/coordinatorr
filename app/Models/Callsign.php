<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Callsign extends Model
{
    use HasFactory;

    public function plannedactivations() : HasMany
    {
        return $this->hasMany(PlannedActivation::class);
    }

    public function activations() : HasMany
    {
        return $this->hasMany(Activation::class);
    }
}
