<?php

namespace App\Services\Imports;

use App\Models\Team;
use App\Models\TournamentMatch;
use Illuminate\Support\Facades\DB;

class WorldCupFixtureImportService
{
    /**
     * @param  array<int, array<string, mixed>>  $fixtures
     * @return array{created: int, updated: int, skipped: int}
     */
    public function import(int $tournamentId, array $fixtures): array
    {
        return DB::transaction(function () use ($tournamentId, $fixtures): array {
            $summary = ['created' => 0, 'updated' => 0, 'skipped' => 0];

            foreach ($fixtures as $fixture) {
                if (! $this->isValidFixture($fixture)) {
                    $summary['skipped']++;

                    continue;
                }

                $match = $this->findMatch($tournamentId, $fixture) ?? new TournamentMatch([
                    'tournament_id' => $tournamentId,
                ]);

                $match->fill($this->payload($tournamentId, $fixture, $match));
                $match->save();

                $match->wasRecentlyCreated ? $summary['created']++ : $summary['updated']++;
            }

            return $summary;
        });
    }

    /**
     * @param  array<string, mixed>  $fixture
     */
    private function isValidFixture(array $fixture): bool
    {
        return filled($fixture['starts_at'] ?? null)
            && filled($fixture['stage'] ?? null)
            && (
                filled($fixture['match_number'] ?? null)
                || filled($fixture['home_name'] ?? null)
                || filled($fixture['home_placeholder'] ?? null)
            )
            && (
                filled($fixture['away_name'] ?? null)
                || filled($fixture['away_placeholder'] ?? null)
            );
    }

    /**
     * @param  array<string, mixed>  $fixture
     */
    private function findMatch(int $tournamentId, array $fixture): ?TournamentMatch
    {
        if (filled($fixture['match_number'] ?? null)) {
            return TournamentMatch::query()
                ->where('tournament_id', $tournamentId)
                ->where('match_number', $fixture['match_number'])
                ->first();
        }

        return TournamentMatch::query()
            ->where('tournament_id', $tournamentId)
            ->where('starts_at', $fixture['starts_at'])
            ->where('home_placeholder', $fixture['home_placeholder'] ?? null)
            ->where('away_placeholder', $fixture['away_placeholder'] ?? null)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $fixture
     * @return array<string, mixed>
     */
    private function payload(int $tournamentId, array $fixture, TournamentMatch $match): array
    {
        $homeTeamId = $this->teamId($tournamentId, $fixture['home_name'] ?? null);
        $awayTeamId = $this->teamId($tournamentId, $fixture['away_name'] ?? null);

        return [
            'tournament_id' => $tournamentId,
            'match_number' => $fixture['match_number'] ?? $match->match_number,
            'stage' => $fixture['stage'],
            'group_name' => $fixture['group_name'] ?? null,
            'starts_at' => $fixture['starts_at'],
            'home_team_id' => $homeTeamId ?? $match->home_team_id,
            'away_team_id' => $awayTeamId ?? $match->away_team_id,
            'home_placeholder' => $homeTeamId ? null : ($fixture['home_placeholder'] ?? $match->home_placeholder),
            'away_placeholder' => $awayTeamId ? null : ($fixture['away_placeholder'] ?? $match->away_placeholder),
            'stadium' => $fixture['stadium'] ?? $match->stadium,
            'city' => $fixture['city'] ?? $match->city,
            'source' => 'google_html',
            'source_payload' => $fixture,
            'status' => $match->status ?? 'scheduled',
        ];
    }

    private function teamId(int $tournamentId, ?string $name): ?int
    {
        if (! filled($name)) {
            return null;
        }

        return Team::query()->firstOrCreate([
            'tournament_id' => $tournamentId,
            'name' => $name,
        ])->id;
    }
}
