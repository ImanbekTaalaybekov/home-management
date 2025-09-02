<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fcm_user_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('device');
            $table->string('fcm_token');
            $table->unique(['user_id', 'device'], 'fut_user_device_unique');
            $table->index('fcm_token', 'fut_fcm_token_idx');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fcm_user_tokens');
    }
};
