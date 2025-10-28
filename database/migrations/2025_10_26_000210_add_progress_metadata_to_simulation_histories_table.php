<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('simulation_histories', function (Blueprint $table) {
            $table->json('progress_metadata')->nullable()->after('error_details');
        });
    }

    public function down(): void
    {
        Schema::table('simulation_histories', function (Blueprint $table) {
            $table->dropColumn('progress_metadata');
        });
    }
};
