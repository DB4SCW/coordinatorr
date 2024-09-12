<?php

use App\Models\Band;
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
        Schema::create('bands', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('band', 10)->unique()->index();
            $table->unsignedDecimal('start', 10,4)->index();
            $table->unsignedDecimal('end', 10, 4)->index();
        });

        //Fill band data
        Band::create(['band' => '2190m', 'start' => '0.135', 'end' => '0.139']);
        Band::create(['band' => '630m', 'start' => '0.472', 'end' => '0.48']);
        Band::create(['band' => '160m', 'start' => '1.8', 'end' => '2']);
        Band::create(['band' => '80m', 'start' => '3.5', 'end' => '4']);
        Band::create(['band' => '60m', 'start' => '5', 'end' => '5.9']);
        Band::create(['band' => '40m', 'start' => '7', 'end' => '7.3']);
        Band::create(['band' => '30m', 'start' => '10.1', 'end' => '10.15']);
        Band::create(['band' => '20m', 'start' => '14', 'end' => '14.35']);
        Band::create(['band' => '17m', 'start' => '18.068', 'end' => '18.168']);
        Band::create(['band' => '15m', 'start' => '21', 'end' => '21.45']);
        Band::create(['band' => '12m', 'start' => '24.89', 'end' => '24.99']);
        Band::create(['band' => '10m', 'start' => '28', 'end' => '30']);
        Band::create(['band' => '6m', 'start' => '50', 'end' => '54']);
        Band::create(['band' => '4m', 'start' => '70', 'end' => '71']);
        Band::create(['band' => '2m', 'start' => '144', 'end' => '148']);
        Band::create(['band' => '1.25m', 'start' => '219', 'end' => '225']);
        Band::create(['band' => '70cm', 'start' => '420', 'end' => '450']);
        Band::create(['band' => '33cm', 'start' => '902', 'end' => '928']);
        Band::create(['band' => '23cm', 'start' => '1240', 'end' => '1300']);
        Band::create(['band' => '13cm', 'start' => '2300', 'end' => '2450']);
        Band::create(['band' => '9cm', 'start' => '3400', 'end' => '3475']);
        Band::create(['band' => '6cm', 'start' => '5650', 'end' => '5850']);
        Band::create(['band' => '3cm', 'start' => '10000', 'end' => '10500']);
        Band::create(['band' => '1.25cm', 'start' => '24000', 'end' => '24250']);
        Band::create(['band' => '6mm', 'start' => '47000', 'end' => '47200']);
        Band::create(['band' => '4mm', 'start' => '77500', 'end' => '84000']);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bands');
    }
};
