<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tournament_id', 'nomination_category_id', 'value_text', 'value_number'])]
class NominationResult extends Model
{
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function nominationCategory(): BelongsTo
    {
        return $this->belongsTo(NominationCategory::class);
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
            'value_number' => 'integer',
        ];
    }
}
