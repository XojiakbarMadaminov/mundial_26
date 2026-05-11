<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatchPredictionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tournament_match_id' => $this->tournament_match_id,
            'home_score' => $this->home_score,
            'away_score' => $this->away_score,
            'home_penalty_score' => $this->home_penalty_score,
            'away_penalty_score' => $this->away_penalty_score,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'calculated_at' => $this->calculated_at?->toIso8601String(),
            'points' => $this->when($this->calculated_at !== null, [
                'match_points' => $this->match_points,
                'penalty_points' => $this->penalty_points,
                'total_points' => $this->total_points,
            ]),
        ];
    }
}
