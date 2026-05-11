<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tournament_id', 'home_team_id', 'away_team_id', 'match_number', 'stage', 'group_name', 'starts_at', 'status', 'home_score', 'away_score', 'has_penalty', 'home_penalty_score', 'away_penalty_score', 'points_calculated_at'])]
class TournamentMatch extends Model
{
    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'has_penalty' => false,
        'status' => 'scheduled',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(MatchPrediction::class);
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
            'home_team_id' => 'integer',
            'away_team_id' => 'integer',
            'match_number' => 'integer',
            'starts_at' => 'datetime',
            'home_score' => 'integer',
            'away_score' => 'integer',
            'has_penalty' => 'boolean',
            'home_penalty_score' => 'integer',
            'away_penalty_score' => 'integer',
            'points_calculated_at' => 'datetime',
            'stage' => 'string',
            'status' => 'string',
        ];
    }
}
