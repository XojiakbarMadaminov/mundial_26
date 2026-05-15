<?php

use App\Actions\RecalculateNominationPointsAction;
use App\Actions\SubmitNominationPredictionsAction;
use App\Models\LeaderboardEntry;
use App\Models\NominationCategory;
use App\Models\NominationPrediction;
use App\Models\NominationResult;
use App\Models\Player;
use App\Models\Team;
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

test('correct player selection gives 30', function () {
    $category = new NominationCategory([
        'key' => 'best_player',
        'type' => 'player',
        'points' => 30,
    ]);

    $prediction = new NominationPrediction([
        'player_id' => 5,
    ]);

    $result = new NominationResult([
        'player_id' => 5,
    ]);

    expect(app(NominationService::class)->pointsFor($category, $prediction, $result))->toBe(30);
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

    $player = Player::query()->create([
        'tournament_id' => $tournament->id,
        'name' => 'Lionel Messi',
    ]);

    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    app(SubmitNominationPredictionsAction::class)->execute($tournament, $user, [
        [
            'category_key' => 'best_player',
            'player_id' => $player->id,
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

    $player = Player::query()->create([
        'tournament_id' => $tournament->id,
        'name' => 'Lionel Messi',
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
        'player_id' => $player->id,
    ]);

    NominationResult::query()->create([
        'tournament_id' => $tournament->id,
        'nomination_category_id' => $category->id,
        'player_id' => $player->id,
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

    $teamCategory = NominationCategory::query()->create([
        'tournament_id' => $tournament->id,
        'key' => 'champion',
        'name' => 'Champion',
        'type' => 'team',
        'sort_order' => 20,
    ]);

    $player = Player::query()->create([
        'tournament_id' => $tournament->id,
        'name' => 'Lamine Yamal',
    ]);

    $team = Team::query()->create([
        'tournament_id' => $tournament->id,
        'name' => 'Brazil',
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
                    'player_id' => $player->id,
                ],
                [
                    'category_key' => 'champion',
                    'team_id' => $team->id,
                ],
            ],
        ])
        ->assertSuccessful();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/my-nomination-predictions')
        ->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.player_id', $player->id)
        ->assertJsonPath('data.1.nomination_category_id', $teamCategory->id)
        ->assertJsonPath('data.1.team_id', $team->id);
});

test('nomination player and team options are searchable', function () {
    if (! extension_loaded('pdo_sqlite')) {
        $this->markTestSkipped('The pdo_sqlite extension is not available in this environment.');
    }

    $this->artisan('migrate:fresh')->run();

    $tournament = Tournament::query()->create([
        'name' => 'World Cup 2026',
        'slug' => 'world-cup-2026',
        'status' => 'upcoming',
    ]);

    $team = Team::query()->create([
        'tournament_id' => $tournament->id,
        'name' => 'Brazil',
        'code' => 'BRA',
    ]);

    Player::query()->create([
        'tournament_id' => $tournament->id,
        'team_id' => $team->id,
        'name' => 'Vinicius Junior',
    ]);

    $this->getJson('/api/nomination-options/players?search=Vini')
        ->assertSuccessful()
        ->assertJsonPath('data.0.name', 'Vinicius Junior')
        ->assertJsonPath('data.0.team_name', 'Brazil');

    $this->getJson('/api/nomination-options/teams?search=BRA')
        ->assertSuccessful()
        ->assertJsonPath('data.0.name', 'Brazil')
        ->assertJsonPath('data.0.code', 'BRA');
});
