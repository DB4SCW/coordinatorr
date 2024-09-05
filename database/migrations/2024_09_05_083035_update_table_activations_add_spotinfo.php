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
        Schema::table('activations', function (Blueprint $table) {
            $table->string('hamalert_spotter', 20)->nullable();
            $table->decimal('hamalert_frequency', 12, 6, true);
            $table->string('hamalert_mode', 20)->nullable();
            $table->dateTimeTz('hamalert_spot_datetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activations', function (Blueprint $table) {
            $table->dropColumn('hamalert_spotter');
            $table->dropColumn('hamalert_frequency');
            $table->dropColumn('hamalert_mode');
            $table->dropColumn('hamalert_spot_datetime');
        });
    }
};
