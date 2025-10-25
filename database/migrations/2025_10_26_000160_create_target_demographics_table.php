<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('target_demographics', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->string('value', 100);
            $table->string('display_name');
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['type', 'is_active'], 'idx_demographics_type');
            $table->unique(['type', 'value'], 'unique_demographic');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_demographics');
    }
};
