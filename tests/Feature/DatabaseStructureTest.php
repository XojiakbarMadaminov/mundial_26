<?php

use App\Models\LeaderboardEntry;
use App\Models\MatchPrediction;
use App\Models\NominationCategory;
use App\Models\NominationPrediction;
use App\Models\NominationResult;
use App\Models\Player;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\User;

test('mundial prediction migrations define the expected core tables and constraints', function (string $migration, array $expectedSnippets) {
    $contents = file_get_contents(database_path("migrations/{$migration}"));

    foreach ($expectedSnippets as $snippet) {
        expect($contents)->toContain($snippet);
    }
})->with([
    'users' => ['0001_01_01_000000_create_users_table.php', ['telegram_username', 'phone', "'role', ['admin', 'user']", 'constrained()->nullOnDelete()']],
    'telegram login fields' => ['2026_05_18_172758_add_telegram_login_fields_to_users_table.php', ["string('telegram_id')->nullable()->unique()", "string('telegram_sub')->nullable()->unique()", "string('telegram_photo_url')->nullable()"]],
    'user approval' => ['2026_05_12_110723_add_is_approved_to_users_table.php', ["boolean('is_approved')", 'default(true)', 'index()']],
    'tournaments' => ['2026_05_11_101754_create_tournaments_table.php', ["slug')->unique()", 'prediction_lock_minutes', "'status', ['upcoming', 'active', 'finished']"]],
    'teams' => ['2026_05_11_101755_create_teams_table.php', ["foreignId('tournament_id')->constrained()->cascadeOnDelete()", 'group_name', "index(['tournament_id', 'group_name'])"]],
    'players' => ['2026_05_15_110302_create_players_table.php', ["foreignId('tournament_id')->constrained()->cascadeOnDelete()", "foreignId('team_id')->nullable()->constrained()->nullOnDelete()", "index(['tournament_id', 'name'])"]],
    'tournament matches' => ['2026_05_11_101756_create_tournament_matches_table.php', ['tournament_matches', "constrained('teams')->nullOnDelete()", "'stage', ['group', 'round_32', 'round_16', 'quarter_final', 'semi_final', 'third_place', 'final']", 'points_calculated_at', "unique(['tournament_id', 'match_number'])"]],
    'match predictions' => ['2026_05_11_101757_create_match_predictions_table.php', ['tournament_match_id', 'home_penalty_score', 'penalty_points', 'submitted_at', "unique(['user_id', 'tournament_match_id'])"]],
    'prediction score logs' => ['2026_05_11_101758_create_prediction_score_logs_table.php', ['match_prediction_id', 'description', "index(['match_prediction_id', 'type'])"]],
    'nomination categories' => ['2026_05_11_101759_create_nomination_categories_table.php', ["'type', ['player', 'team', 'number', 'text']", 'sort_order', "unique(['tournament_id', 'key'])"]],
    'nomination predictions' => ['2026_05_11_101800_create_nomination_predictions_table.php', ['nomination_category_id', 'value_number', 'calculated_at', "unique(['user_id', 'nomination_category_id'])"]],
    'nomination select values' => ['2026_05_15_110308_add_select_values_to_nomination_predictions_and_results.php', ["foreignId('player_id')->nullable()", "foreignId('team_id')->nullable()", "Schema::table('nomination_results'"]],
    'nomination results' => ['2026_05_11_101801_create_nomination_results_table.php', ['value_text', 'value_number', "unique(['tournament_id', 'nomination_category_id'])"]],
    'leaderboard entries' => ['2026_05_11_101802_create_leaderboard_entries_table.php', ['nomination_points', 'exact_scores_count', 'goal_difference_count', 'result_count', "unique(['tournament_id', 'user_id'])"]],
]);

test('mundial prediction models expose domain fillable fields and casts', function (object $model, array $fillable, array $casts) {
    expect($model->getFillable())->toEqual($fillable);

    foreach ($casts as $attribute => $cast) {
        expect($model->getCasts())->toHaveKey($attribute, $cast);
    }
})->with([
    'user' => [new User, ['name', 'telegram_id', 'telegram_sub', 'telegram_username', 'telegram_photo_url', 'email', 'phone', 'password', 'role', 'is_approved'], ['password' => 'hashed', 'role' => 'string', 'is_approved' => 'boolean']],
    'tournament' => [new Tournament, ['name', 'slug', 'starts_at', 'ends_at', 'prediction_lock_minutes', 'status'], ['starts_at' => 'datetime', 'prediction_lock_minutes' => 'integer']],
    'player' => [new Player, ['tournament_id', 'team_id', 'name', 'position'], ['tournament_id' => 'integer', 'team_id' => 'integer']],
    'tournament match' => [new TournamentMatch, ['tournament_id', 'home_team_id', 'home_placeholder', 'away_team_id', 'away_placeholder', 'match_number', 'stage', 'group_name', 'starts_at', 'status', 'home_score', 'away_score', 'has_penalty', 'home_penalty_score', 'away_penalty_score', 'points_calculated_at', 'stadium', 'city', 'source', 'source_payload'], ['starts_at' => 'datetime', 'has_penalty' => 'boolean', 'home_score' => 'integer']],
    'match prediction' => [new MatchPrediction, ['tournament_match_id', 'user_id', 'home_score', 'away_score', 'home_penalty_score', 'away_penalty_score', 'match_points', 'penalty_points', 'total_points', 'submitted_at', 'calculated_at'], ['submitted_at' => 'datetime', 'total_points' => 'integer']],
    'nomination category' => [new NominationCategory, ['tournament_id', 'key', 'name', 'type', 'points', 'sort_order'], ['points' => 'integer', 'sort_order' => 'integer']],
    'nomination prediction' => [new NominationPrediction, ['tournament_id', 'nomination_category_id', 'user_id', 'player_id', 'team_id', 'value_text', 'value_number', 'points', 'calculated_at'], ['player_id' => 'integer', 'team_id' => 'integer', 'points' => 'integer']],
    'nomination result' => [new NominationResult, ['tournament_id', 'nomination_category_id', 'player_id', 'team_id', 'value_text', 'value_number'], ['player_id' => 'integer', 'team_id' => 'integer']],
    'leaderboard entry' => [new LeaderboardEntry, ['tournament_id', 'user_id', 'match_points', 'nomination_points', 'total_points', 'exact_scores_count', 'goal_difference_count', 'result_count', 'rank', 'previous_rank', 'rank_changed_at'], ['total_points' => 'integer', 'rank' => 'integer']],
]);
