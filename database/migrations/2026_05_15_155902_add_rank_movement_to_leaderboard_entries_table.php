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
        Schema::table('leaderboard_entries', function (Blueprint $table) {
            $table->unsignedInteger('previous_rank')->nullable()->after('rank');
            $table->timestamp('rank_changed_at')->nullable()->after('previous_rank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaderboard_entries', function (Blueprint $table) {
            $table->dropColumn(['previous_rank', 'rank_changed_at']);
        });
    }
};
