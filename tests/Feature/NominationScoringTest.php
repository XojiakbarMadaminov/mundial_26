<?php

use App\Actions\RecalculateNominationPointsAction;
use App\Actions\SubmitNominationPredictionsAction;
use App\Models\LeaderboardEntry;
use App\Models\NominationCategory;
use App\Models\NominationPrediction;
use App\Models\NominationResult;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\User;
use App\Services\NominationService;
use Illuminate\Validation\ValidationException;

function nominationPoints(string $type, mixed $predictionValue, mixed $resultValue): int
{
    $category = new NominationCategory([
        'key' => 'best_player',
        'type' => $type,
        'points' => 30,
    ]);

    $prediction = new NominationPrediction([
        'value_text' => $type === 'number' ? null : $predictionValue,
        'value_number' => $type === 'number' ? $predictionValue : null,
    ]);

    $result = new NominationResult([
        'value_text' => $type === 'number' ? null : $resultValue,
        'value_number' => $type === 'number' ? $resultValue : null,
    ]);

    return app(NominationService::class)->pointsFor($category, $prediction, $result);
}

test('correct text nomination gives 30', function () {
    expect(nominationPoints('player', '  Lionel Messi ', 'lionel messi'))->toBe(30);
});

test('wrong text gives 0', function () {
    expect(nominationPoints('team', 'Brazil', 'Argentina'))->toBe(0);
});

test('correct number gives 30', function () {
    expect(nominationPoints('number', 7, 7))->toBe(30);
});

test('update blocked after tournament starts', function () {
    if (! extension_loaded('pdo_sqlite')) {
        $this->markTestSkipped('The pdo_sqlite extension is not available in this environment.');
    }

    $this->artisan('migrate:fresh')->run();

    $tournament = Tournament::query()->create([
        'name' => 'World Cup 2026',
        'slug' => 'world-cup-2026',
    ]);

    TournamentMatch::query()->create([
        'tournament_id' => $tournament->id,
        'stage' => 'group',
        'starts_at' => now()->subMinute(),
    ]);

    NominationCategory::query()->create([
        'tournament_id' => $tournament->id,
        'key' => 'best_player',
        'name' => 'Best player',
        'type' => 'player',
    ]);

    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    app(SubmitNominationPredictionsAction::class)->execute($tournament, $user, [
        [
            'category_key' => 'best_player',
            'value_text' => 'Lionel Messi',
        ],
    ]);
})->throws(ValidationException::class);

test('repeated recalculation does not duplicate points', function () {
    if (! extension_loaded('pdo_sqlite')) {
        $this->markTestSkipped('The pdo_sqlite extension is not available in this environment.');
    }

    $this->artisan('migrate:fresh')->run();

    $tournament = Tournament::query()->create([
        'name' => 'World Cup 2026',
        'slug' => 'world-cup-2026',
    ]);

    $category = NominationCategory::query()->create([
        'tournament_id' => $tournament->id,
        'key' => 'best_player',
        'name' => 'Best player',
        'type' => 'player',
    ]);

    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    NominationPrediction::query()->create([
        'tournament_id' => $tournament->id,
        'nomination_category_id' => $category->id,
        'user_id' => $user->id,
        'value_text' => 'lionel messi',
    ]);

    NominationResult::query()->create([
        'tournament_id' => $tournament->id,
        'nomination_category_id' => $category->id,
        'value_text' => 'Lionel Messi',
    ]);

    $action = app(RecalculateNominationPointsAction::class);

    $action->execute($tournament);
    $action->execute($tournament->id);

    $prediction = NominationPrediction::query()->firstOrFail();
    $leaderboardEntry = LeaderboardEntry::query()->firstOrFail();

    expect($prediction->points)->toBe(30)
        ->and(NominationPrediction::query()->sum('points'))->toBe(30)
        ->and(LeaderboardEntry::query()->count())->toBe(1)
        ->and($leaderboardEntry->nomination_points)->toBe(30)
        ->and($leaderboardEntry->total_points)->toBe(30);
});

test('partial nomination predictions can be fetched after saving', function () {
    if (! extension_loaded('pdo_sqlite')) {
        $this->markTestSkipped('The pdo_sqlite extension is not available in this environment.');
    }

    $this->artisan('migrate:fresh')->run();

    $tournament = Tournament::query()->create([
        'name' => 'World Cup 2026',
        'slug' => 'world-cup-2026',
        'status' => 'upcoming',
    ]);

    NominationCategory::query()->create([
        'tournament_id' => $tournament->id,
        'key' => 'best_player',
        'name' => 'Best player',
        'type' => 'player',
    ]);

    NominationCategory::query()->create([
        'tournament_id' => $tournament->id,
        'key' => 'champion',
        'name' => 'Champion',
        'type' => 'team',
        'sort_order' => 20,
    ]);

    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/nominations/predictions', [
            'predictions' => [
                [
                    'category_key' => 'best_player',
                    'value_text' => 'Lamine Yamal',
                    'value_number' => null,
                ],
            ],
        ])
        ->assertSuccessful();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/my-nomination-predictions')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.value_text', 'lamine yamal');
});
