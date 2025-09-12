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
            $table->string('login')->nullable();
            $table->string('personal_account')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('name');
            $table->string('password');
            $table->string('block_number')->nullable();
            $table->string('apartment_number')->nullable();
            $table->string('non_residential_premises')->nullable();
            $table->foreignId('residential_complex_id')->nullable()->constrained('residential_complexes');
            $table->string('fcm_token')->nullable();
            $table->string('role')->nullable();
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
