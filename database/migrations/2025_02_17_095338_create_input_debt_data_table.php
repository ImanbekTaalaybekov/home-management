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
        Schema::create('input_debt_data', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->nullable();
            $table->string('full_name')->nullable();
            $table->string('address')->nullable();
            $table->string('apartment_number')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('debt_month')->nullable();
            $table->decimal('housing_maintenance', 10, 2)->nullable();
            $table->decimal('hot_water_sewage_meter', 10, 2)->nullable();
            $table->decimal('heating', 10, 2)->nullable();
            $table->decimal('garbage_disposal', 10, 2)->nullable();
            $table->decimal('cold_water_meter', 10, 2)->nullable();
            $table->decimal('electricity', 10, 2)->nullable();
            $table->decimal('hot_water_meter', 10, 2)->nullable();
            $table->decimal('cold_water_sewage_meter', 10, 2)->nullable();
            $table->decimal('previous_debts', 10, 2)->nullable();
            $table->decimal('duty_lighting', 10, 2)->nullable();
            $table->decimal('capital_repair', 10, 2)->nullable();
            $table->decimal('total_utilities', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('input_debt_data');
    }
};
