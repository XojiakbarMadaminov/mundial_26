<?php

namespace App\Http\Resources;

use App\Models\MatchPrediction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lockAt = $this->starts_at->copy()->subMinutes($this->tournament->prediction_lock_minutes);
        $myPrediction = $this->myPrediction();
        $homeDisplayName = $this->relationLoaded('homeTeam') ? $this->homeTeam?->name : null;
        $awayDisplayName = $this->relationLoaded('awayTeam') ? $this->awayTeam?->name : null;

        return [
            'id' => $this->id,
            'tournament_id' => $this->tournament_id,
            'match_number' => $this->match_number,
            'stage' => $this->stage,
            'group_name' => $this->group_name,
            'status' => $this->status,
            'starts_at' => $this->starts_at->toIso8601String(),
            'lock_at' => $lockAt->toIso8601String(),
            'is_prediction_locked' => now()->gte($lockAt),
            'has_penalty' => $this->has_penalty,
            'home_team' => TeamResource::make($this->whenLoaded('homeTeam')),
            'away_team' => TeamResource::make($this->whenLoaded('awayTeam')),
            'home_display_name' => $homeDisplayName ?? $this->home_placeholder,
            'away_display_name' => $awayDisplayName ?? $this->away_placeholder,
            'my_prediction' => $myPrediction ? MatchPredictionResource::make($myPrediction) : null,
            'result' => $this->when($this->status === 'finished', [
                'home_score' => $this->home_score,
                'away_score' => $this->away_score,
                'home_penalty_score' => $this->home_penalty_score,
                'away_penalty_score' => $this->away_penalty_score,
            ]),
            'points' => $this->when($myPrediction?->calculated_at !== null, [
                'match_points' => $myPrediction?->match_points,
                'penalty_points' => $myPrediction?->penalty_points,
                'total_points' => $myPrediction?->total_points,
            ]),
        ];
    }

    private function myPrediction(): ?MatchPrediction
    {
        if (! $this->relationLoaded('predictions')) {
            return null;
        }

        return $this->predictions->first();
    }
}
