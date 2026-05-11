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
            'value_text' => $this->value_text,
            'value_number' => $this->value_number,
            'points' => $this->points,
            'calculated_at' => $this->calculated_at?->toIso8601String(),
        ];
    }
}
