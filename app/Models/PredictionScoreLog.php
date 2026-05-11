<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['match_prediction_id', 'type', 'points', 'description'])]
class PredictionScoreLog extends Model
{
    public function matchPrediction(): BelongsTo
    {
        return $this->belongsTo(MatchPrediction::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'match_prediction_id' => 'integer',
            'points' => 'integer',
        ];
    }
}
