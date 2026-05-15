<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LeaderboardEntryResource;
use App\Models\LeaderboardEntry;
use App\Models\Tournament;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LeaderboardController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return LeaderboardEntryResource::collection(
            LeaderboardEntry::query()
                ->with('user')
                ->where('tournament_id', $this->currentTournament()->id)
                ->orderByRaw('rank is null')
                ->orderBy('rank')
                ->orderByDesc('total_points')
                ->get()
        );
    }

    private function currentTournament(): Tournament
    {
        return Tournament::resolveCurrent();
    }
}
