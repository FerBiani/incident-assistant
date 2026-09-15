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
        DB::table('incident_notes')->whereIn('source', ['AI', 'Ai', 'aI', 'ai'])->update(['source' => 'ai']);
        DB::table('incident_notes')
            ->where(fn ($query) => $query->whereNull('source')->orWhere('source', '!=', 'ai'))
            ->update(['source' => 'user']);

        Schema::table('incident_notes', function (Blueprint $table) {
            $table->string('source', 20)->default('user')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incident_notes', function (Blueprint $table) {
            $table->text('source')->nullable()->default(null)->change();
        });
    }
};
