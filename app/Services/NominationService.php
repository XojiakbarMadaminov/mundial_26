<?php

namespace App\Services;

use App\Models\NominationCategory;
use App\Models\NominationPrediction;
use App\Models\NominationResult;
use App\Models\Tournament;
use Carbon\CarbonInterface;

class NominationService
{
    /**
     * @return array<string, array{name: string, type: string, points: int, sort_order: int}>
     */
    public function defaultCategories(): array
    {
        return [
            'best_player' => ['name' => 'Best player', 'type' => 'player', 'points' => 30, 'sort_order' => 10],
            'best_goalkeeper' => ['name' => 'Best goalkeeper', 'type' => 'player', 'points' => 30, 'sort_order' => 20],
            'goalkeeper_conceded_goals' => ['name' => 'Best goalkeeper conceded goals count', 'type' => 'number', 'points' => 30, 'sort_order' => 30],
            'top_scorer' => ['name' => 'Top scorer', 'type' => 'player', 'points' => 30, 'sort_order' => 40],
            'top_scorer_goals' => ['name' => 'Top scorer goals count', 'type' => 'number', 'points' => 30, 'sort_order' => 50],
            'champion' => ['name' => 'Champion', 'type' => 'team', 'points' => 30, 'sort_order' => 60],
            'worst_team' => ['name' => 'Worst team', 'type' => 'team', 'points' => 30, 'sort_order' => 70],
        ];
    }

    public function pointsFor(NominationCategory $category, NominationPrediction $prediction, ?NominationResult $result): int
    {
        if ($result === null) {
            return 0;
        }

        if ($category->type === 'number') {
            return $prediction->value_number !== null
                && $result->value_number !== null
                && $prediction->value_number === $result->value_number
                    ? $category->points
                    : 0;
        }

        return $prediction->value_text !== null
            && $result->value_text !== null
            && $this->normalizeText($prediction->value_text) === $this->normalizeText($result->value_text)
                ? $category->points
                : 0;
    }

    public function normalizeText(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    public function firstMatchStartsAt(Tournament $tournament): ?CarbonInterface
    {
        return $tournament->matches()
            ->orderBy('starts_at')
            ->first(['starts_at'])
            ?->starts_at;
    }

    public function predictionsAreLocked(Tournament $tournament): bool
    {
        $firstMatchStartsAt = $this->firstMatchStartsAt($tournament);

        return $firstMatchStartsAt !== null && now()->gte($firstMatchStartsAt);
    }
}
