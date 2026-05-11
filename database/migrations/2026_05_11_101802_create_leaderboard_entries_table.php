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
        Schema::create('leaderboard_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('match_points')->default(0);
            $table->unsignedInteger('nomination_points')->default(0);
            $table->unsignedInteger('total_points')->default(0);
            $table->unsignedInteger('exact_scores_count')->default(0);
            $table->unsignedInteger('goal_difference_count')->default(0);
            $table->unsignedInteger('result_count')->default(0);
            $table->unsignedInteger('rank')->nullable();
            $table->timestamps();

            $table->unique(['tournament_id', 'user_id']);
            $table->index(['tournament_id', 'rank']);
            $table->index(['tournament_id', 'total_points']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaderboard_entries');
    }
};
