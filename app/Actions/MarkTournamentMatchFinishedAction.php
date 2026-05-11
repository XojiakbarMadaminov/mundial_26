<?php

namespace App\Actions;

use App\Models\TournamentMatch;
use DomainException;

class MarkTournamentMatchFinishedAction
{
    public function execute(TournamentMatch $match): void
    {
        if ($match->home_score === null || $match->away_score === null) {
            throw new DomainException('Match result is incomplete.');
        }

        if ($match->has_penalty && ($match->home_penalty_score === null || $match->away_penalty_score === null)) {
            throw new DomainException('Penalty result is incomplete.');
        }

        $match->update(['status' => 'finished']);
    }
}
