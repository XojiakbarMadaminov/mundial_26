<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NominationPredictionResource extends JsonResource
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
            'tournament_id' => $this->tournament_id,
            'category' => NominationCategoryApiResource::make($this->whenLoaded('nominationCategory')),
            'nomination_category_id' => $this->nomination_category_id,
            'player_id' => $this->player_id,
            'player' => $this->player ? [
                'id' => $this->player->id,
                'name' => $this->player->name,
            ] : null,
            'team_id' => $this->team_id,
            'team' => $this->team ? [
                'id' => $this->team->id,
                'name' => $this->team->name,
            ] : null,
            'value_text' => $this->value_text,
            'value_number' => $this->value_number,
            'points' => $this->points,
            'calculated_at' => $this->calculated_at?->toIso8601String(),
        ];
    }
}
