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
        Schema::create('input_debt_data_urtas', function (Blueprint $table) {
            $table->id();
            $table->string('account_number');
            $table->string('management_body_code');
            $table->string('management_body_name');
            $table->string('supplier_code');
            $table->string('supplier_name');
            $table->string('owner_full_name');
            $table->string('region');
            $table->string('locality');
            $table->string('locality_part')->nullable();
            $table->string('house');
            $table->string('apartment')->nullable();
            $table->string('service');
            $table->integer('debt_months_count');
            $table->date('last_payment_date')->nullable();
            $table->decimal('debt_amount', 15, 2);
            $table->decimal('current_charges', 15, 2)->nullable();
            $table->string('document_type')->nullable();
            $table->date('document_date')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('input_debt_data_urtas');
    }
};
