<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulation_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_session_id', 100)->nullable();
            $table->json('input_data');
            $table->json('output_data')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('n8n_workflow_id')->nullable();
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('processing_completed_at')->nullable();
            $table->integer('processing_duration_seconds')->nullable();
            $table->json('error_details')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('guest_session_id')->references('session_id')->on('guest_sessions')->nullOnDelete();

            $table->index('guest_session_id', 'idx_simulations_guest');
            $table->index('n8n_workflow_id', 'idx_simulations_n8n');
            $table->index('deleted_at', 'idx_simulations_deleted');
            $table->index(['user_id', 'created_at'], 'idx_simulations_user_recent');
            $table->index(['status', 'user_id', 'created_at'], 'idx_simulations_filter');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE simulation_histories
                ADD COLUMN product_name VARCHAR(255)
                AS (JSON_UNQUOTE(JSON_EXTRACT(output_data, '$.product_name'))) STORED,
                ADD INDEX idx_simulations_product_name (product_name)
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('simulation_histories');
    }
};
