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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('personal_account')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('name')->unique();
            $table->string('password');
            $table->string('block_number')->nullable();
            $table->string('apartment_number')->nullable();
            $table->string('residential_complex_id')->nullable();
            $table->string('fcm_token')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
