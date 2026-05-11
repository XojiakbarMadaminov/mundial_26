<?php

use App\Actions\RecalculateMatchPointsAction;
use App\Models\LeaderboardEntry;
use App\Models\MatchPrediction;
use App\Models\PredictionScoreLog;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\User;
use App\Services\MatchScoreCalculator;

function scorePrediction(array $matchAttributes, array $predictionAttributes): array
{
    return app(MatchScoreCalculator::class)->calculate(
        new TournamentMatch(array_merge([
            'home_score' => 2,
            'away_score' => 1,
            'has_penalty' => false,
        ], $matchAttributes)),
        new MatchPrediction(array_merge([
            'home_score' => 0,
            'away_score' => 0,
        ], $predictionAttributes)),
    );
}

test('exact score gives 10', function () {
    $score = scorePrediction([], [
        'home_score' => 2,
        'away_score' => 1,
    ]);

    expect($score['match_points'])->toBe(10)
        ->and($score['penalty_points'])->toBe(0)
        ->and($score['total_points'])->toBe(10)
        ->and($score['logs'])->toHaveCount(1)
        ->and($score['logs'][0]['type'])->toBe('exact_score');
});

test('correct difference gives 4', function () {
    $score = scorePrediction([], [
        'home_score' => 3,
        'away_score' => 2,
    ]);

    expect($score['match_points'])->toBe(4)
        ->and($score['logs'])->toHaveCount(1)
        ->and($score['logs'][0]['type'])->toBe('goal_difference');
});

test('correct result gives 1', function () {
    $score = scorePrediction([], [
        'home_score' => 4,
        'away_score' => 0,
    ]);

    expect($score['match_points'])->toBe(1)
        ->and($score['logs'])->toHaveCount(1)
        ->and($score['logs'][0]['type'])->toBe('result');
});

test('wrong prediction gives 0', function () {
    $score = scorePrediction([], [
        'home_score' => 0,
        'away_score' => 1,
    ]);

    expect($score['match_points'])->toBe(0)
        ->and($score['penalty_points'])->toBe(0)
        ->and($score['total_points'])->toBe(0)
        ->and($score['logs'])->toBe([]);
});

test('exact score does not also add difference or result', function () {
    $score = scorePrediction([], [
        'home_score' => 2,
        'away_score' => 1,
    ]);

    expect($score['match_points'])->toBe(10)
        ->and($score['total_points'])->toBe(10)
        ->and(collect($score['logs'])->pluck('type')->all())->toBe(['exact_score']);
});

test('exact penalty score gives 10', function () {
    $score = scorePrediction([
        'home_score' => 1,
        'away_score' => 1,
        'has_penalty' => true,
        'home_penalty_score' => 4,
        'away_penalty_score' => 3,
    ], [
        'home_score' => 0,
        'away_score' => 0,
        'home_penalty_score' => 4,
        'away_penalty_score' => 3,
    ]);

    expect($score['penalty_points'])->toBe(10)
        ->and(collect($score['logs'])->pluck('type')->contains('penalty_exact_score'))->toBeTrue();
});

test('penalty winner gives 2', function () {
    $score = scorePrediction([
        'home_score' => 1,
        'away_score' => 1,
        'has_penalty' => true,
        'home_penalty_score' => 4,
        'away_penalty_score' => 3,
    ], [
        'home_score' => 0,
        'away_score' => 0,
        'home_penalty_score' => 5,
        'away_penalty_score' => 4,
    ]);

    expect($score['penalty_points'])->toBe(2)
        ->and(collect($score['logs'])->pluck('type')->contains('penalty_winner'))->toBeTrue();
});

test('repeated recalculation does not duplicate points', function () {
    if (! extension_loaded('pdo_sqlite')) {
        $this->markTestSkipped('The pdo_sqlite extension is not available in this environment.');
    }

    $this->artisan('migrate:fresh')->run();

    $tournament = Tournament::query()->create([
        'name' => 'World Cup 2026',
        'slug' => 'world-cup-2026',
    ]);

    $match = TournamentMatch::query()->create([
        'tournament_id' => $tournament->id,
        'stage' => 'group',
        'starts_at' => now(),
        'status' => 'finished',
        'home_score' => 2,
        'away_score' => 1,
    ]);

    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    MatchPrediction::query()->create([
        'tournament_match_id' => $match->id,
        'user_id' => $user->id,
        'home_score' => 2,
        'away_score' => 1,
        'submitted_at' => now(),
    ]);

    $action = app(RecalculateMatchPointsAction::class);

    $action->execute($match);
    $action->execute($match->fresh());

    $prediction = MatchPrediction::query()->firstOrFail();
    $leaderboardEntry = LeaderboardEntry::query()->firstOrFail();

    expect($prediction->match_points)->toBe(10)
        ->and($prediction->penalty_points)->toBe(0)
        ->and($prediction->total_points)->toBe(10)
        ->and(PredictionScoreLog::query()->count())->toBe(1)
        ->and($leaderboardEntry->match_points)->toBe(10)
        ->and($leaderboardEntry->total_points)->toBe(10)
        ->and($leaderboardEntry->exact_scores_count)->toBe(1);
});
