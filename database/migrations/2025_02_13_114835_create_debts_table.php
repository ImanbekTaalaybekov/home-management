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
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('type');
            $table->string('name')->nullable();
            $table->decimal('amount', 10, 2);
            $table->decimal('current_charges', 10, 2);
            $table->date('due_date')->nullable();
            $table->decimal('payment_amount', 10, 2)->nullable();
            $table->decimal('initial_amount', 10, 2)->nullable();
            $table->decimal('period_start_balance', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
