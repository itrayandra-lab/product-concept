<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('inci_name')->unique();
            $table->text('description')->nullable();
            $table->json('effects')->nullable();
            $table->text('safety_notes')->nullable();
            $table->json('concentration_ranges')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('ingredient_categories')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->json('scientific_references')->nullable();
            $table->timestamps();

            $table->index('name', 'idx_ingredients_name');
            $table->index('category_id', 'idx_ingredients_category');
            $table->index('is_active', 'idx_ingredients_active');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::table('ingredients', function (Blueprint $table) {
                $table->fullText(['name', 'inci_name', 'description'], 'idx_ingredients_search');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
