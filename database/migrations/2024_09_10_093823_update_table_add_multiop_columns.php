<?php

use App\Models\Mode;
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
        Schema::create('modes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('mode', 20);
        });

        $mode1 = new Mode();
        $mode1->mode = 'CW';
        $mode1->save();

        $mode1 = new Mode();
        $mode1->mode = 'VOICE';
        $mode1->save();

        $mode1 = new Mode();
        $mode1->mode = 'DIGITAL';
        $mode1->save();

        //add keys to activations
        Schema::table('activations', function (Blueprint $table) {
            $table->foreignId('mode_id')->nullable();
            $table->foreignId('band_id')->nullable();
        });

        //add keys to planned activations
        Schema::table('planned_activations', function (Blueprint $table) {
            $table->foreignId('mode_id')->nullable();
            $table->foreignId('band_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       //drop table modes
       Schema::dropIfExists('modes');

       Schema::table('activations', function (Blueprint $table) {
        $table->dropColumn('mode_id');
        $table->dropColumn('band_id');
       });

       Schema::table('planned_activations', function (Blueprint $table) {
        $table->dropColumn('mode_id');
        $table->dropColumn('band_id');
       });
    }
};
