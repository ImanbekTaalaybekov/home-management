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
        Schema::create('input_debt_data_ivcs', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->nullable();
            $table->string('house')->nullable();
            $table->string('apartment')->nullable();
            $table->string('full_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('service_name')->nullable();
            $table->decimal('debt', 10, 2)->nullable();
            $table->decimal('penalty', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('input_debt_data_ivcs');
    }
};
