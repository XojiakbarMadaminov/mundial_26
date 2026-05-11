<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'starts_at', 'ends_at', 'prediction_lock_minutes', 'status'])]
class Tournament extends Model
{
    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'prediction_lock_minutes' => 120,
        'status' => 'upcoming',
    ];

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(TournamentMatch::class);
    }

    public function nominationCategories(): HasMany
    {
        return $this->hasMany(NominationCategory::class);
    }

    public function nominationPredictions(): HasMany
    {
        return $this->hasMany(NominationPrediction::class);
    }

    public function nominationResults(): HasMany
    {
        return $this->hasMany(NominationResult::class);
    }

    public function leaderboardEntries(): HasMany
    {
        return $this->hasMany(LeaderboardEntry::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'prediction_lock_minutes' => 'integer',
            'status' => 'string',
        ];
    }
}
