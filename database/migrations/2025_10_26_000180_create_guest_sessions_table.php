<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100)->unique();
            $table->json('form_data');
            $table->string('form_step', 50)->nullable();
            $table->json('completed_steps')->nullable();
            $table->decimal('form_progress', 5, 2)->default(0.00);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index('expires_at', 'idx_guest_sessions_expires');
            $table->index('created_at', 'idx_guest_sessions_created');
            $table->index(['expires_at', 'created_at'], 'idx_guest_sessions_cleanup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_sessions');
    }
};
