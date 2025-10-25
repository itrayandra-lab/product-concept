<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scientific_references', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->text('abstract')->nullable();
            $table->json('authors')->nullable();
            $table->string('journal')->nullable();
            $table->date('publication_date')->nullable();
            $table->string('doi')->nullable()->unique();
            $table->string('pubmed_id', 20)->nullable()->unique();
            $table->text('url')->nullable();
            $table->enum('reference_type', ['journal', 'clinical_trial', 'review', 'book', 'patent', 'other'])->default('journal');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('doi', 'idx_references_doi');
            $table->index('pubmed_id', 'idx_references_pubmed');
            $table->index('reference_type', 'idx_references_type');
            $table->index('publication_date', 'idx_references_date');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::table('scientific_references', function (Blueprint $table) {
                $table->fullText(['title', 'abstract'], 'idx_references_search');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('scientific_references');
    }
};
