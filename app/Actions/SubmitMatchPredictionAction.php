<?php

namespace App\Actions;

use App\Models\MatchPrediction;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmitMatchPredictionAction
{
    public function execute(TournamentMatch $match, User $user, array $data, bool $allowUpdate = false): MatchPrediction
    {
        return DB::transaction(function () use ($match, $user, $data, $allowUpdate): MatchPrediction {
            $lockedMatch = TournamentMatch::query()
                ->with('tournament')
                ->whereKey($match->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureMatchIsOpen($lockedMatch);
            $this->ensurePenaltyPredictionIsAllowed($lockedMatch, $data);

            $prediction = MatchPrediction::query()
                ->where('tournament_match_id', $lockedMatch->id)
                ->where('user_id', $user->id)
                ->first();

            if ($prediction !== null && ! $allowUpdate) {
                throw ValidationException::withMessages([
                    'match' => 'You already submitted a prediction for this match.',
                ]);
            }

            if ($prediction === null) {
                $prediction = new MatchPrediction([
                    'tournament_match_id' => $lockedMatch->id,
                    'user_id' => $user->id,
                ]);
            }

            $prediction->fill([
                'home_score' => $data['home_score'],
                'away_score' => $data['away_score'],
                'home_penalty_score' => $data['home_penalty_score'] ?? null,
                'away_penalty_score' => $data['away_penalty_score'] ?? null,
                'match_points' => 0,
                'penalty_points' => 0,
                'total_points' => 0,
                'submitted_at' => now(),
                'calculated_at' => null,
            ])->save();

            return $prediction->load('tournamentMatch.tournament', 'tournamentMatch.homeTeam', 'tournamentMatch.awayTeam');
        });
    }

    private function ensureMatchIsOpen(TournamentMatch $match): void
    {
        $lockAt = $match->starts_at->copy()->subMinutes($match->tournament->prediction_lock_minutes);

        if (now()->gte($lockAt)) {
            throw ValidationException::withMessages([
                'match' => 'Prediction is locked for this match.',
            ]);
        }
    }

    private function ensurePenaltyPredictionIsAllowed(TournamentMatch $match, array $data): void
    {
        $hasPenaltyPrediction = ($data['home_penalty_score'] ?? null) !== null || ($data['away_penalty_score'] ?? null) !== null;

        if ($hasPenaltyPrediction && ! in_array($match->stage, $this->playoffStages(), true)) {
            throw ValidationException::withMessages([
                'home_penalty_score' => 'Penalty prediction is only allowed for playoff matches.',
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    private function playoffStages(): array
    {
        return ['round_32', 'round_16', 'quarter_final', 'semi_final', 'third_place', 'final'];
    }
}
