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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['global', 'complex', 'personal']);
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('residential_complex_id')->nullable()->constrained();
            $table->string('document')->nullable();
            $table->enum('type', ['technical', 'common']);
            $table->integer('client_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
