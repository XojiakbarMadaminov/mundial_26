<?php

namespace App\Actions;

use App\Models\LeaderboardEntry;
use App\Models\MatchPrediction;
use App\Models\NominationPrediction;
use App\Models\PredictionScoreLog;
use App\Models\Tournament;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RecalculateLeaderboardAction
{
    public function execute(Tournament|int $tournament): void
    {
        $tournamentId = $tournament instanceof Tournament ? $tournament->id : $tournament;

        DB::transaction(function () use ($tournamentId): void {
            $userIds = $this->userIdsForTournament($tournamentId);

            foreach ($userIds as $userId) {
                $matchPoints = $this->matchPointsFor($tournamentId, $userId);
                $nominationPoints = $this->nominationPointsFor($tournamentId, $userId);

                LeaderboardEntry::query()->updateOrCreate(
                    [
                        'tournament_id' => $tournamentId,
                        'user_id' => $userId,
                    ],
                    [
                        'match_points' => $matchPoints,
                        'nomination_points' => $nominationPoints,
                        'total_points' => $matchPoints + $nominationPoints,
                        'exact_scores_count' => $this->scoreLogCountFor($tournamentId, $userId, 'exact_score'),
                        'goal_difference_count' => $this->scoreLogCountFor($tournamentId, $userId, 'goal_difference'),
                        'result_count' => $this->scoreLogCountFor($tournamentId, $userId, 'result'),
                    ],
                );
            }

            $this->assignRanks($tournamentId);
        });
    }

    /**
     * @return Collection<int, int>
     */
    private function userIdsForTournament(int $tournamentId): Collection
    {
        $matchUserIds = MatchPrediction::query()
            ->whereHas('tournamentMatch', fn ($query) => $query->where('tournament_id', $tournamentId))
            ->pluck('user_id');

        $nominationUserIds = NominationPrediction::query()
            ->where('tournament_id', $tournamentId)
            ->pluck('user_id');

        return $matchUserIds
            ->merge($nominationUserIds)
            ->unique()
            ->values();
    }

    private function matchPointsFor(int $tournamentId, int $userId): int
    {
        return (int) MatchPrediction::query()
            ->where('user_id', $userId)
            ->whereHas('tournamentMatch', fn ($query) => $query->where('tournament_id', $tournamentId))
            ->sum('total_points');
    }

    private function nominationPointsFor(int $tournamentId, int $userId): int
    {
        return (int) NominationPrediction::query()
            ->where('tournament_id', $tournamentId)
            ->where('user_id', $userId)
            ->sum('points');
    }

    private function scoreLogCountFor(int $tournamentId, int $userId, string $type): int
    {
        return PredictionScoreLog::query()
            ->where('type', $type)
            ->whereHas('matchPrediction', function ($query) use ($tournamentId, $userId): void {
                $query
                    ->where('user_id', $userId)
                    ->whereHas('tournamentMatch', fn ($query) => $query->where('tournament_id', $tournamentId));
            })
            ->count();
    }

    private function assignRanks(int $tournamentId): void
    {
        LeaderboardEntry::query()
            ->where('tournament_id', $tournamentId)
            ->orderByDesc('total_points')
            ->orderByDesc('exact_scores_count')
            ->orderByDesc('goal_difference_count')
            ->orderByDesc('result_count')
            ->orderBy('user_id')
            ->get()
            ->each(function (LeaderboardEntry $entry, int $index): void {
                $newRank = $index + 1;
                $oldRank = $entry->rank;

                $entry->update(
                    $oldRank !== null && $oldRank !== $newRank
                        ? [
                            'rank' => $newRank,
                            'previous_rank' => $oldRank,
                            'rank_changed_at' => now(),
                        ]
                        : ['rank' => $newRank],
                );
            });
    }
}
