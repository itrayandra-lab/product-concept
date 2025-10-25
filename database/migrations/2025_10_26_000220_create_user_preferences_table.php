<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('form_defaults')->nullable();
            $table->json('notification_settings')->nullable();
            $table->json('ui_preferences')->nullable();
            $table->timestamps();

            $table->unique('user_id', 'unique_user_preferences');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
