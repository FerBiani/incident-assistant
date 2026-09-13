<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->text('ai_summary')->nullable()->after('status');
            $table->string('ai_severity')->nullable()->after('ai_summary');
            $table->json('ai_probable_causes')->nullable()->after('ai_severity');
            $table->json('ai_recommended_actions')->nullable()->after('ai_probable_causes');
            $table->timestamp('ai_analyzed_at')->nullable()->after('ai_recommended_actions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn('ai_summary');
            $table->dropColumn('ai_severity');
            $table->dropColumn('ai_probable_causes');
            $table->dropColumn('ai_recommended_actions');
            $table->dropColumn('ai_analyzed_at');
        });
    }
};
