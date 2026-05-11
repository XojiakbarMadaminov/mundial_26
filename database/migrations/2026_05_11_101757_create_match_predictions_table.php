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
        Schema::create('match_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_match_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('home_score');
            $table->unsignedInteger('away_score');
            $table->unsignedInteger('home_penalty_score')->nullable();
            $table->unsignedInteger('away_penalty_score')->nullable();
            $table->unsignedInteger('match_points')->default(0);
            $table->unsignedInteger('penalty_points')->default(0);
            $table->unsignedInteger('total_points')->default(0);
            $table->dateTime('submitted_at');
            $table->dateTime('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'tournament_match_id']);
            $table->index(['tournament_match_id', 'total_points']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_predictions');
    }
};
