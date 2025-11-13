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
        Schema::create('debt_name_translations', function (Blueprint $table) {
            $table->id();
            $table->string('original')->nullable();
            $table->string('ru')->nullable();
            $table->string('kg')->nullable();
            $table->string('uz')->nullable();
            $table->string('kk')->nullable();
            $table->string('en')->nullable();
            $table->string('es')->nullable();
            $table->string('zh')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debt_name_translations');
    }
};
