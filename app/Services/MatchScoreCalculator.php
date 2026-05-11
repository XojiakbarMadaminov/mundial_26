<?php

namespace App\Services;

use App\Models\MatchPrediction;
use App\Models\TournamentMatch;
use DomainException;

class MatchScoreCalculator
{
    /**
     * @return array{match_points: int, penalty_points: int, total_points: int, logs: array<int, array{type: string, points: int, description: string}>}
     */
    public function calculate(TournamentMatch $match, MatchPrediction $prediction): array
    {
        $this->ensureMatchCanBeScored($match);

        $matchPoints = $this->calculateMainPoints($match, $prediction);
        $penaltyPoints = $this->calculatePenaltyPoints($match, $prediction);

        return [
            'match_points' => $matchPoints['points'],
            'penalty_points' => $penaltyPoints['points'],
            'total_points' => $matchPoints['points'] + $penaltyPoints['points'],
            'logs' => array_values(array_filter([
                $matchPoints['log'],
                $penaltyPoints['log'],
            ])),
        ];
    }

    /**
     * @return array{points: int, log: null|array{type: string, points: int, description: string}}
     */
    private function calculateMainPoints(TournamentMatch $match, MatchPrediction $prediction): array
    {
        if ($prediction->home_score === $match->home_score && $prediction->away_score === $match->away_score) {
            return $this->score('exact_score', 10, 'Exact score prediction.');
        }

        $predictedDifference = $prediction->home_score - $prediction->away_score;
        $actualDifference = $match->home_score - $match->away_score;

        if ($predictedDifference === $actualDifference) {
            return $this->score('goal_difference', 4, 'Correct goal difference prediction.');
        }

        if ($this->resultFor($prediction->home_score, $prediction->away_score) === $this->resultFor($match->home_score, $match->away_score)) {
            return $this->score('result', 1, 'Correct match result prediction.');
        }

        return ['points' => 0, 'log' => null];
    }

    /**
     * @return array{points: int, log: null|array{type: string, points: int, description: string}}
     */
    private function calculatePenaltyPoints(TournamentMatch $match, MatchPrediction $prediction): array
    {
        if (! $match->has_penalty) {
            return ['points' => 0, 'log' => null];
        }

        if ($prediction->home_penalty_score === null || $prediction->away_penalty_score === null) {
            return ['points' => 0, 'log' => null];
        }

        if ($prediction->home_penalty_score === $match->home_penalty_score && $prediction->away_penalty_score === $match->away_penalty_score) {
            return $this->score('penalty_exact_score', 10, 'Exact penalty score prediction.');
        }

        if ($this->resultFor($prediction->home_penalty_score, $prediction->away_penalty_score) === $this->resultFor($match->home_penalty_score, $match->away_penalty_score)) {
            return $this->score('penalty_winner', 2, 'Correct penalty winner prediction.');
        }

        return ['points' => 0, 'log' => null];
    }

    private function ensureMatchCanBeScored(TournamentMatch $match): void
    {
        if ($match->home_score === null || $match->away_score === null) {
            throw new DomainException('Match result is incomplete.');
        }

        if ($match->has_penalty && ($match->home_penalty_score === null || $match->away_penalty_score === null)) {
            throw new DomainException('Penalty result is incomplete.');
        }
    }

    /**
     * @return array{points: int, log: array{type: string, points: int, description: string}}
     */
    private function score(string $type, int $points, string $description): array
    {
        return [
            'points' => $points,
            'log' => [
                'type' => $type,
                'points' => $points,
                'description' => $description,
            ],
        ];
    }

    private function resultFor(int $homeScore, int $awayScore): string
    {
        return match ($homeScore <=> $awayScore) {
            1 => 'home_win',
            -1 => 'away_win',
            default => 'draw',
        };
    }
}
