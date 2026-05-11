<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TournamentResource;
use App\Models\Tournament;

class TournamentController extends Controller
{
    public function current(): TournamentResource
    {
        return TournamentResource::make($this->currentTournament());
    }

    private function currentTournament(): Tournament
    {
        return Tournament::query()
            ->where('status', 'active')
            ->orderBy('starts_at')
            ->first()
            ?? Tournament::query()
                ->where('status', 'upcoming')
                ->orderBy('starts_at')
                ->firstOrFail();
    }
}
