<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulation_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simulation_id')->constrained('simulation_histories')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->decimal('concentration_percentage', 5, 2)->nullable();
            $table->string('concentration_unit', 20)->nullable()->default('%');
            $table->text('custom_notes')->nullable();
            $table->timestamps();

            $table->unique(['simulation_id', 'ingredient_id'], 'unique_simulation_ingredient');
            $table->index('simulation_id', 'idx_sim_ingredients_simulation');
            $table->index('ingredient_id', 'idx_sim_ingredients_ingredient');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulation_ingredients');
    }
};
