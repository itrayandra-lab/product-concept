<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('company')->nullable();

            $table->enum('provider', ['local', 'google'])->default('local');
            $table->string('provider_id')->nullable();
            $table->text('avatar_url')->nullable();

            $table->enum('subscription_tier', ['free', 'premium', 'enterprise'])->default('free');
            $table->json('permissions')->nullable();

            $table->boolean('terms_accepted')->default(false);
            $table->timestamp('terms_accepted_at')->nullable();

            $table->integer('daily_simulation_count')->default(0);
            $table->date('last_simulation_date')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['provider', 'provider_id'], 'idx_users_provider');
            $table->index('subscription_tier', 'idx_users_subscription');
            $table->index(['last_simulation_date', 'daily_simulation_count'], 'idx_users_rate_limit');
            $table->index('deleted_at', 'idx_users_deleted');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE users
                ADD COLUMN subscription_expires DATE
                AS (JSON_UNQUOTE(JSON_EXTRACT(permissions, '$.subscription_expires'))) STORED,
                ADD INDEX idx_users_subscription_expires (subscription_expires)
            ");
        }

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
