<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['tournament_id', 'key', 'name', 'type', 'points', 'sort_order'])]
class NominationCategory extends Model
{
    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'points' => 30,
        'sort_order' => 0,
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(NominationPrediction::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(NominationResult::class);
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
            'points' => 'integer',
            'sort_order' => 'integer',
            'type' => 'string',
        ];
    }
}
