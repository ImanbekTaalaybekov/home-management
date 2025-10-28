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
        Schema::create('analytics_alseco_data', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->nullable();
            $table->string('management_code')->nullable();
            $table->string('management_name')->nullable();
            $table->string('supplier_code')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('region')->nullable();
            $table->string('locality')->nullable();
            $table->string('locality_part')->nullable();
            $table->string('house')->nullable();
            $table->string('apartment')->nullable();
            $table->string('full_name')->nullable();
            $table->integer('people_count')->nullable();
            $table->integer('supplier_people_count')->nullable();
            $table->decimal('area', 10, 2)->nullable();
            $table->decimal('tariff', 10, 2)->nullable();
            $table->string('service')->nullable();
            $table->decimal('balance_start', 15, 2)->nullable();
            $table->decimal('balance_change', 15, 2)->nullable();
            $table->decimal('initial_accrual', 15, 2)->nullable();
            $table->decimal('accrual_change', 15, 2)->nullable();
            $table->decimal('accrual_end', 15, 2)->nullable();
            $table->string('payment_date')->nullable();
            $table->decimal('payment', 15, 2)->nullable();
            $table->decimal('payment_transfer', 15, 2)->nullable();
            $table->decimal('balance_end', 15, 2)->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_alseco_data');
    }
};
