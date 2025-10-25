<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_cache', function (Blueprint $table) {
            $table->id();
            $table->string('search_term');
            $table->string('platform', 50);
            $table->json('search_filters')->nullable();
            $table->json('results');
            $table->integer('result_count')->default(0);
            $table->timestamp('cached_at');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['search_term', 'platform'], 'idx_market_cache_search');
            $table->index('expires_at', 'idx_market_cache_expires');
            $table->index(['platform', 'cached_at'], 'idx_market_cache_platform');
            $table->index(['search_term', 'platform', 'expires_at'], 'idx_market_cache_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_cache');
    }
};
