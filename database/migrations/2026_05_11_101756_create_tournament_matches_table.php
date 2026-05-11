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
        Schema::create('tournament_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('home_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('away_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->unsignedInteger('match_number')->nullable();
            $table->enum('stage', ['group', 'round_32', 'round_16', 'quarter_final', 'semi_final', 'third_place', 'final']);
            $table->string('group_name')->nullable();
            $table->dateTime('starts_at');
            $table->enum('status', ['scheduled', 'live', 'finished'])->default('scheduled');
            $table->unsignedInteger('home_score')->nullable();
            $table->unsignedInteger('away_score')->nullable();
            $table->boolean('has_penalty')->default(false);
            $table->unsignedInteger('home_penalty_score')->nullable();
            $table->unsignedInteger('away_penalty_score')->nullable();
            $table->dateTime('points_calculated_at')->nullable();
            $table->timestamps();

            $table->index(['tournament_id', 'starts_at']);
            $table->index(['tournament_id', 'stage']);
            $table->index(['tournament_id', 'status']);
            $table->unique(['tournament_id', 'match_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_matches');
    }
};
