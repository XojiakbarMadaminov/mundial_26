<?php

use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Services\Imports\GoogleWorldCupHtmlParser;
use App\Services\Imports\WorldCupFixtureImportService;

function sampleFixture(array $overrides = []): array
{
    return array_merge([
        'match_number' => 1,
        'stage' => 'group',
        'group_name' => 'A',
        'home_name' => 'Mexico',
        'away_name' => 'South Africa',
        'home_placeholder' => null,
        'away_placeholder' => null,
        'starts_at' => '2026-06-11 19:00:00',
        'timezone' => 'UTC',
        'stadium' => 'Estadio Azteca',
        'city' => 'Mexico City',
        'raw_text' => 'Match 1 Mexico vs South Africa',
    ], $overrides);
}

function prepareFixtureImportDatabase(): void
{
    if (! extension_loaded('pdo_sqlite')) {
        test()->markTestSkipped('The pdo_sqlite extension is not available in this environment.');
    }

    test()->artisan('migrate:fresh')->run();
}

test('parser returns fixtures from sample html', function () {
    $fixtures = app(GoogleWorldCupHtmlParser::class)->parse(
        (string) file_get_contents(resource_path('matches.html'))
    );

    $first = $fixtures[0];
    $playoff = collect($fixtures)->firstWhere('stage', 'round_32');

    expect($fixtures)->toHaveCount(104)
        ->and($first['stage'])->toBe('group')
        ->and($first['group_name'])->toBe('A')
        ->and($first['home_name'])->toBe('Meksika')
        ->and($first['starts_at'])->toBe('2026-06-11 19:00:00')
        ->and($playoff['home_placeholder'])->toBe('Н/Д')
        ->and($playoff['away_placeholder'])->toBe('Н/Д');
});

test('import creates matches', function () {
    prepareFixtureImportDatabase();

    $tournament = Tournament::query()->create([
        'name' => 'World Cup 2026',
        'slug' => 'world-cup-2026',
    ]);

    $summary = app(WorldCupFixtureImportService::class)->import($tournament->id, [
        sampleFixture(),
    ]);

    expect($summary)->toBe(['created' => 1, 'updated' => 0, 'skipped' => 0])
        ->and(TournamentMatch::query()->count())->toBe(1);
});

test('repeated import updates not duplicates', function () {
    prepareFixtureImportDatabase();

    $tournament = Tournament::query()->create([
        'name' => 'World Cup 2026',
        'slug' => 'world-cup-2026',
    ]);

    $service = app(WorldCupFixtureImportService::class);

    $service->import($tournament->id, [sampleFixture()]);
    $summary = $service->import($tournament->id, [sampleFixture(['stadium' => 'Updated Stadium'])]);

    expect($summary)->toBe(['created' => 0, 'updated' => 1, 'skipped' => 0])
        ->and(TournamentMatch::query()->count())->toBe(1)
        ->and(TournamentMatch::query()->first()->stadium)->toBe('Updated Stadium');
});

test('unknown teams are saved as placeholders', function () {
    prepareFixtureImportDatabase();

    $tournament = Tournament::query()->create([
        'name' => 'World Cup 2026',
        'slug' => 'world-cup-2026',
    ]);

    app(WorldCupFixtureImportService::class)->import($tournament->id, [
        sampleFixture([
            'match_number' => 73,
            'stage' => 'round_32',
            'group_name' => null,
            'home_name' => null,
            'away_name' => null,
            'home_placeholder' => 'Winner Group A',
            'away_placeholder' => 'Runner-up Group B',
            'starts_at' => '2026-06-28 19:00:00',
        ]),
    ]);

    $match = TournamentMatch::query()->firstOrFail();

    expect($match->home_team_id)->toBeNull()
        ->and($match->away_team_id)->toBeNull()
        ->and($match->home_placeholder)->toBe('Winner Group A')
        ->and($match->away_placeholder)->toBe('Runner-up Group B');
});

test('known teams are created and linked', function () {
    prepareFixtureImportDatabase();

    $tournament = Tournament::query()->create([
        'name' => 'World Cup 2026',
        'slug' => 'world-cup-2026',
    ]);

    app(WorldCupFixtureImportService::class)->import($tournament->id, [
        sampleFixture(),
    ]);

    $match = TournamentMatch::query()->firstOrFail();

    expect(Team::query()->count())->toBe(2)
        ->and($match->homeTeam->name)->toBe('Mexico')
        ->and($match->awayTeam->name)->toBe('South Africa');
});

test('dry run does not create database records', function () {
    prepareFixtureImportDatabase();

    $tournament = Tournament::query()->create([
        'name' => 'World Cup 2026',
        'slug' => 'world-cup-2026',
    ]);

    $this->artisan('mundial:import-matches-html', [
        'file' => resource_path('matches.html'),
        '--tournament_id' => $tournament->id,
        '--dry-run' => true,
    ])->assertSuccessful();

    expect(TournamentMatch::query()->count())->toBe(0);
});

test('command uses default matches html file when file argument is missing', function () {
    prepareFixtureImportDatabase();

    $tournament = Tournament::query()->create([
        'name' => 'World Cup 2026',
        'slug' => 'world-cup-2026',
    ]);

    $this->artisan('mundial:import-matches-html', [
        '--tournament_id' => $tournament->id,
        '--dry-run' => true,
    ])->assertSuccessful();

    expect(TournamentMatch::query()->count())->toBe(0);
});
