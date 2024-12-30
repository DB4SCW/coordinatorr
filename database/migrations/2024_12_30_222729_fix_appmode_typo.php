<?php

use App\Models\Appmode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $mode = Appmode::where('option', 'SINGLEOP')->first();
        $mode->description = 'Only one operator may use a callsign at one time.';
        $mode->save();

        $mode = Appmode::where('option', 'MULTIOPBAND')->first();
        $mode->description = 'Multiple operators may use a callsign on different bands simultaneously.';
        $mode->save();

        $mode = Appmode::where('option', 'MULTIOPMODE')->first();
        $mode->description = 'Multiple operators may use a callsign on different modes simultaneously, even on the same band.';
        $mode->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //nothing to change
    }
};
