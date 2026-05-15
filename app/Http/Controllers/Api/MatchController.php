<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MatchResource;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MatchController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return MatchResource::collection(
            $this->matchQuery($request)
                ->orderBy('starts_at')
                ->orderBy('match_number')
                ->get()
        );
    }

    public function today(Request $request): AnonymousResourceCollection
    {
        return MatchResource::collection(
            $this->matchQuery($request)
                ->whereDate('starts_at', today())
                ->orderBy('starts_at')
                ->orderBy('match_number')
                ->get()
        );
    }

    public function show(Request $request, TournamentMatch $match): MatchResource
    {
        return MatchResource::make(
            $this->withMatchRelations($request, $match->newQuery())
                ->whereKey($match->getKey())
                ->firstOrFail()
        );
    }

    private function matchQuery(Request $request)
    {
        return $this->withMatchRelations($request, TournamentMatch::query())
            ->where('tournament_id', $this->currentTournament()->id);
    }

    private function withMatchRelations(Request $request, $query)
    {
        $query->with(['tournament', 'homeTeam', 'awayTeam']);

        if ($request->user('sanctum')) {
            $query->with([
                'predictions' => fn ($query) => $query->where('user_id', $request->user('sanctum')->id),
            ]);
        }

        return $query;
    }

    private function currentTournament(): Tournament
    {
        return Tournament::resolveCurrent();
    }
}
