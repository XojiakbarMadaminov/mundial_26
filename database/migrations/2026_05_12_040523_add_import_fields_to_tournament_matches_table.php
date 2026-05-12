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
        Schema::table('tournament_matches', function (Blueprint $table): void {
            $table->string('home_placeholder')->nullable()->after('home_team_id');
            $table->string('away_placeholder')->nullable()->after('away_team_id');
            $table->string('stadium')->nullable()->after('away_penalty_score');
            $table->string('city')->nullable()->after('stadium');
            $table->string('source')->nullable()->after('city');
            $table->json('source_payload')->nullable()->after('source');
        });

        Schema::table('teams', function (Blueprint $table): void {
            $table->unique(['tournament_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table): void {
            $table->dropUnique(['tournament_id', 'name']);
        });

        Schema::table('tournament_matches', function (Blueprint $table): void {
            $table->dropColumn([
                'home_placeholder',
                'away_placeholder',
                'stadium',
                'city',
                'source',
                'source_payload',
            ]);
        });
    }
};
