<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tournament_id', 'nomination_category_id', 'player_id', 'team_id', 'value_text', 'value_number'])]
class NominationResult extends Model
{
    protected static function booted(): void
    {
        static::saving(function (NominationResult $result): void {
            $type = NominationCategory::query()
                ->whereKey($result->nomination_category_id)
                ->value('type');

            if ($type !== 'player') {
                $result->player_id = null;
            }

            if ($type !== 'team') {
                $result->team_id = null;
            }

            if ($type !== 'text') {
                $result->value_text = null;
            }

            if ($type !== 'number') {
                $result->value_number = null;
            }
        });
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function nominationCategory(): BelongsTo
    {
        return $this->belongsTo(NominationCategory::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tournament_id' => 'integer',
            'nomination_category_id' => 'integer',
            'player_id' => 'integer',
            'team_id' => 'integer',
            'value_number' => 'integer',
        ];
    }
}
