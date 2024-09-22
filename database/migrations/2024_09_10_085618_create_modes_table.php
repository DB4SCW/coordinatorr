<?php

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
        //Create table
        Schema::create('appmodes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('option', 20);
            $table->string('description', 255);
        });

        //Create available modes
        $singleop = new App\Models\Appmode();
        $singleop->option = 'SINGLEOP';
        $singleop->description = 'Only one operator my use a callsign at one time.';
        $singleop->save();

        $multiopband = new App\Models\Appmode();
        $multiopband->option = 'MULTIOPBAND';
        $multiopband->description = 'Multiple operators my use a callsign on different bands simultaneously.';
        $multiopband->save();

        $multiopmode = new App\Models\Appmode();
        $multiopmode->option = 'MULTIOPMODE';
        $multiopmode->description = 'Multiple operators my use a callsign on different modes simultaneously, even on the same band.';
        $multiopmode->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //drop table
        Schema::dropIfExists('appmodes');
    }
};
