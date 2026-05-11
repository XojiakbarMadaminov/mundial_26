<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tournament_match_id', 'user_id', 'home_score', 'away_score', 'home_penalty_score', 'away_penalty_score', 'match_points', 'penalty_points', 'total_points', 'submitted_at', 'calculated_at'])]
class MatchPrediction extends Model
{
    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'match_points' => 0,
        'penalty_points' => 0,
        'total_points' => 0,
    ];

    public function tournamentMatch(): BelongsTo
    {
        return $this->belongsTo(TournamentMatch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scoreLogs(): HasMany
    {
        return $this->hasMany(PredictionScoreLog::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tournament_match_id' => 'integer',
            'user_id' => 'integer',
            'home_score' => 'integer',
            'away_score' => 'integer',
            'home_penalty_score' => 'integer',
            'away_penalty_score' => 'integer',
            'match_points' => 'integer',
            'penalty_points' => 'integer',
            'total_points' => 'integer',
            'submitted_at' => 'datetime',
            'calculated_at' => 'datetime',
        ];
    }
}
