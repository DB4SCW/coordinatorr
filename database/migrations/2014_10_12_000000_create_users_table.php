<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20)->unique();
            $table->string('password');
            $table->boolean('admin')->default(false);
            $table->timestamps();
        });

        //Create initial admin user
        $user = new User();
        $user->name = 'administrator';
        $user->password = bcrypt('welcome#01');
        $user->admin = true;
        $user->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
