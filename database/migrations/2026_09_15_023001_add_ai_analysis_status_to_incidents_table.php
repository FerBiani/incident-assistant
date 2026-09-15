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
        Schema::table('incidents', function (Blueprint $table) {
            $table->string('ai_analysis_status', 20)->default('not_started')->after('ai_analyzed_at');
        });

        DB::table('incidents')
            ->whereNotNull('ai_analyzed_at')
            ->update(['ai_analysis_status' => 'completed']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn('ai_analysis_status');
        });
    }
};
