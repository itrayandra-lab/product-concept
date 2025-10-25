<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredient_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->foreignId('reference_id')->constrained('scientific_references')->cascadeOnDelete();
            $table->enum('relevance_level', ['primary', 'secondary', 'supporting'])->default('supporting');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['ingredient_id', 'reference_id'], 'unique_ingredient_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredient_references');
    }
};
