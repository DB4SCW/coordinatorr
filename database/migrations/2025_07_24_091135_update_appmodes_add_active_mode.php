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
        Schema::table('appmodes', function (Blueprint $table) {
            $table->boolean('active')->default(false);
        });

        $current_appmode = Appmode::where('option', config('app.db4scw_coordinatorr_mode'))->get();

        if($current_appmode->count() != 1)
        {
            $default_appmode = Appmode::where('option', 'SINGLEOP')->first();
            $current_appmode = $default_appmode;
        }else{
            $current_appmode = $current_appmode->first();
        }

        $current_appmode->active = true;
        $current_appmode->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appmodes', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
};
