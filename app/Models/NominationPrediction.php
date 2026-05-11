<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tournament_id', 'nomination_category_id', 'user_id', 'value_text', 'value_number', 'points', 'calculated_at'])]
class NominationPrediction extends Model
{
    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'points' => 0,
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function nominationCategory(): BelongsTo
    {
        return $this->belongsTo(NominationCategory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
            'user_id' => 'integer',
            'value_number' => 'integer',
            'points' => 'integer',
            'calculated_at' => 'datetime',
        ];
    }
}
