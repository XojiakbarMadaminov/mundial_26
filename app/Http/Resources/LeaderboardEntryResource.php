<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaderboardEntryResource extends JsonResource
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
            'rank' => $this->rank,
            'user' => [
                'id' => $this->user_id,
                'name' => $this->whenLoaded('user', fn () => $this->user->name),
            ],
            'match_points' => $this->match_points,
            'nomination_points' => $this->nomination_points,
            'total_points' => $this->total_points,
            'exact_scores_count' => $this->exact_scores_count,
            'goal_difference_count' => $this->goal_difference_count,
            'result_count' => $this->result_count,
            'previous_rank' => $this->previous_rank,
            'rank_changed_at' => $this->rank_changed_at?->toIso8601String(),
        ];
    }
}
