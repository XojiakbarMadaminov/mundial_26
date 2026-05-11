<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tournament_id', 'user_id', 'match_points', 'nomination_points', 'total_points', 'exact_scores_count', 'goal_difference_count', 'result_count', 'rank'])]
class LeaderboardEntry extends Model
{
    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'match_points' => 0,
        'nomination_points' => 0,
        'total_points' => 0,
        'exact_scores_count' => 0,
        'goal_difference_count' => 0,
        'result_count' => 0,
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
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
            'user_id' => 'integer',
            'match_points' => 'integer',
            'nomination_points' => 'integer',
            'total_points' => 'integer',
            'exact_scores_count' => 'integer',
            'goal_difference_count' => 'integer',
            'result_count' => 'integer',
            'rank' => 'integer',
        ];
    }
}
