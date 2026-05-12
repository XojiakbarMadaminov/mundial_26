<?php

use App\Models\LeaderboardEntry;
use App\Models\MatchPrediction;
use App\Models\NominationCategory;
use App\Models\NominationPrediction;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Support\Facades\Route;

function prepareComparisonDatabase(): void
{
    if (! extension_loaded('pdo_sqlite')) {
        test()->markTestSkipped('The pdo_sqlite extension is not available in this environment.');
    }

    test()->artisan('migrate:fresh')->run();
}

test('comparison route is registered', function (string $method, string $uri, ?string $routeUri = null) {
    $route = Route::getRoutes()->match(request: request()->create($uri, $method));

    expect($route->uri())->toBe($routeUri ?? ltrim($uri, '/'));
})->with([
    'comparison' => ['GET', '/api/comparison/1', 'api/comparison/{user}'],
]);

test('comparison endpoint returns side-by-side stats for two users', function () {
    prepareComparisonDatabase();

    $tournament = Tournament::query()->create([
        'name' => 'World Cup 2026',
        'slug' => 'world-cup-2026',
        'status' => 'active',
        'prediction_lock_minutes' => 120,
        'starts_at' => now(),
    ]);

    $homeTeam = Team::query()->create([
        'tournament_id' => $tournament->id,
        'name' => 'Uzbekistan',
        'code' => 'UZB',
    ]);

    $awayTeam = Team::query()->create([
        'tournament_id' => $tournament->id,
        'name' => 'Brazil',
        'code' => 'BRA',
    ]);

    $match = TournamentMatch::query()->create([
        'tournament_id' => $tournament->id,
        'home_team_id' => $homeTeam->id,
        'away_team_id' => $awayTeam->id,
        'stage' => 'group',
        'starts_at' => now()->addDay(),
        'status' => 'finished',
        'home_score' => 2,
        'away_score' => 1,
    ]);

    $category = NominationCategory::query()->create([
        'tournament_id' => $tournament->id,
        'key' => 'top_scorer',
        'name' => 'Top scorer',
        'type' => 'player',
        'points' => 30,
        'sort_order' => 1,
    ]);

    $me = User::query()->create([
        'name' => 'Me',
        'email' => 'me@example.com',
        'password' => 'password',
        'is_approved' => true,
    ]);

    $opponent = User::query()->create([
        'name' => 'Opponent',
        'email' => 'opponent@example.com',
        'password' => 'password',
        'is_approved' => true,
    ]);

    LeaderboardEntry::query()->create([
        'tournament_id' => $tournament->id,
        'user_id' => $me->id,
        'match_points' => 10,
        'nomination_points' => 30,
        'total_points' => 40,
        'exact_scores_count' => 1,
        'goal_difference_count' => 0,
        'result_count' => 0,
        'rank' => 1,
    ]);

    LeaderboardEntry::query()->create([
        'tournament_id' => $tournament->id,
        'user_id' => $opponent->id,
        'match_points' => 4,
        'nomination_points' => 0,
        'total_points' => 4,
        'exact_scores_count' => 0,
        'goal_difference_count' => 1,
        'result_count' => 0,
        'rank' => 2,
    ]);

    MatchPrediction::query()->create([
        'tournament_match_id' => $match->id,
        'user_id' => $me->id,
        'home_score' => 2,
        'away_score' => 1,
        'submitted_at' => now(),
        'calculated_at' => now(),
        'match_points' => 10,
        'penalty_points' => 0,
        'total_points' => 10,
    ]);

    MatchPrediction::query()->create([
        'tournament_match_id' => $match->id,
        'user_id' => $opponent->id,
        'home_score' => 3,
        'away_score' => 2,
        'submitted_at' => now(),
        'calculated_at' => now(),
        'match_points' => 4,
        'penalty_points' => 0,
        'total_points' => 4,
    ]);

    NominationPrediction::query()->create([
        'tournament_id' => $tournament->id,
        'nomination_category_id' => $category->id,
        'user_id' => $me->id,
        'value_text' => 'Player One',
        'points' => 30,
        'calculated_at' => now(),
    ]);

    NominationPrediction::query()->create([
        'tournament_id' => $tournament->id,
        'nomination_category_id' => $category->id,
        'user_id' => $opponent->id,
        'value_text' => 'Player Two',
        'points' => 0,
        'calculated_at' => now(),
    ]);

    $this->actingAs($me, 'sanctum')
        ->getJson('/api/comparison/'.$opponent->id)
        ->assertOk()
        ->assertJsonPath('me.name', 'Me')
        ->assertJsonPath('opponent.name', 'Opponent')
        ->assertJsonPath('me.total_points', 40)
        ->assertJsonPath('opponent.total_points', 4)
        ->assertJsonPath('matches.0.me_prediction.home_score', 2)
        ->assertJsonPath('matches.0.opponent_prediction.home_score', 3)
        ->assertJsonPath('nominations.0.me_prediction.value_text', 'Player One')
        ->assertJsonPath('nominations.0.opponent_prediction.value_text', 'Player Two');
});
