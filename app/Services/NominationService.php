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
            'top_scorer' => ['name' => "To'purar", 'type' => 'player', 'points' => 30, 'sort_order' => 10],
            'top_scorer_goals' => ['name' => "To'purar urgan gollari", 'type' => 'number', 'points' => 30, 'sort_order' => 20],
            'best_player' => ['name' => 'Eng yaxshi futbolchi', 'type' => 'player', 'points' => 30, 'sort_order' => 30],
            'best_goalkeeper' => ['name' => 'Eng yaxshi darvozabon', 'type' => 'player', 'points' => 30, 'sort_order' => 40],
            'goalkeeper_conceded_goals' => ['name' => "Eng yaxshi darvozabon o'tkazgan gollari", 'type' => 'number', 'points' => 30, 'sort_order' => 50],
            'champion' => ['name' => 'Champion', 'type' => 'team', 'points' => 30, 'sort_order' => 60],
            'worst_team' => ['name' => 'Muvaffaqiyatsiz jamoa', 'type' => 'team', 'points' => 30, 'sort_order' => 70],
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

        if ($category->type === 'player') {
            return $this->sameSelectedValue($prediction->player_id, $result->player_id)
                || $this->sameTextValue($prediction->value_text, $result->value_text)
                    ? $category->points
                    : 0;
        }

        if ($category->type === 'team') {
            return $this->sameSelectedValue($prediction->team_id, $result->team_id)
                || $this->sameTextValue($prediction->value_text, $result->value_text)
                    ? $category->points
                    : 0;
        }

        return $this->sameTextValue($prediction->value_text, $result->value_text)
            ? $category->points
            : 0;
    }

    private function sameSelectedValue(?int $predictionValue, ?int $resultValue): bool
    {
        return $predictionValue !== null
            && $resultValue !== null
            && $predictionValue === $resultValue;
    }

    private function sameTextValue(?string $predictionValue, ?string $resultValue): bool
    {
        return $predictionValue !== null
            && $resultValue !== null
            && $this->normalizeText($predictionValue) === $this->normalizeText($resultValue);
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
