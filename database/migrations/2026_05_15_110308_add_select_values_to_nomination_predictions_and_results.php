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
        Schema::table('nomination_predictions', function (Blueprint $table) {
            $table->foreignId('player_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->foreignId('team_id')->nullable()->after('player_id')->constrained()->nullOnDelete();

            $table->index(['tournament_id', 'player_id']);
            $table->index(['tournament_id', 'team_id']);
        });

        Schema::table('nomination_results', function (Blueprint $table) {
            $table->foreignId('player_id')->nullable()->after('nomination_category_id')->constrained()->nullOnDelete();
            $table->foreignId('team_id')->nullable()->after('player_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nomination_results', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_id');
            $table->dropConstrainedForeignId('player_id');
        });

        Schema::table('nomination_predictions', function (Blueprint $table) {
            $table->dropIndex(['tournament_id', 'team_id']);
            $table->dropIndex(['tournament_id', 'player_id']);
            $table->dropConstrainedForeignId('team_id');
            $table->dropConstrainedForeignId('player_id');
        });
    }
};
