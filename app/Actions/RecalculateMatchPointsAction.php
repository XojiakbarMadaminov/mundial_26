<?php

namespace App\Actions;

use App\Models\PredictionScoreLog;
use App\Models\TournamentMatch;
use App\Services\MatchScoreCalculator;
use Illuminate\Support\Facades\DB;

class RecalculateMatchPointsAction
{
    public function __construct(
        private readonly MatchScoreCalculator $calculator,
        private readonly RecalculateLeaderboardAction $recalculateLeaderboard,
    ) {}

    public function execute(TournamentMatch $match): void
    {
        $tournamentId = DB::transaction(function () use ($match): int {
            $lockedMatch = TournamentMatch::query()
                ->with('predictions')
                ->whereKey($match->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            throw_if($lockedMatch->status !== 'finished', new \DomainException('Only finished matches can be calculated.'));

            PredictionScoreLog::query()
                ->whereIn('match_prediction_id', $lockedMatch->predictions()->select('id'))
                ->delete();

            $lockedMatch->predictions()->update([
                'match_points' => 0,
                'penalty_points' => 0,
                'total_points' => 0,
                'calculated_at' => null,
            ]);

            foreach ($lockedMatch->predictions as $prediction) {
                $score = $this->calculator->calculate($lockedMatch, $prediction);

                $prediction->update([
                    'match_points' => $score['match_points'],
                    'penalty_points' => $score['penalty_points'],
                    'total_points' => $score['total_points'],
                    'calculated_at' => now(),
                ]);

                foreach ($score['logs'] as $log) {
                    $prediction->scoreLogs()->create($log);
                }
            }

            $lockedMatch->update([
                'points_calculated_at' => now(),
            ]);

            return $lockedMatch->tournament_id;
        });

        $this->recalculateLeaderboard->execute($tournamentId);
    }
}
