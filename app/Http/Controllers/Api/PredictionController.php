<?php

namespace App\Http\Controllers\Api;

use App\Actions\SubmitMatchPredictionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePredictionRequest;
use App\Http\Requests\UpdatePredictionRequest;
use App\Http\Resources\MatchPredictionResource;
use App\Models\MatchPrediction;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PredictionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return MatchPredictionResource::collection(
            MatchPrediction::query()
                ->with(['tournamentMatch.tournament', 'tournamentMatch.homeTeam', 'tournamentMatch.awayTeam'])
                ->where('user_id', $request->user()->id)
                ->whereHas('tournamentMatch', fn ($query) => $query->where('tournament_id', $this->currentTournament()->id))
                ->join('tournament_matches', 'match_predictions.tournament_match_id', '=', 'tournament_matches.id')
                ->orderBy('tournament_matches.starts_at')
                ->select('match_predictions.*')
                ->get()
        );
    }

    public function store(StorePredictionRequest $request, TournamentMatch $match, SubmitMatchPredictionAction $action): JsonResponse
    {
        return MatchPredictionResource::make(
            $action->execute($match, $request->user(), $request->validated())
        )->response()->setStatusCode(201);
    }

    public function update(UpdatePredictionRequest $request, TournamentMatch $match, SubmitMatchPredictionAction $action): MatchPredictionResource
    {
        return MatchPredictionResource::make(
            $action->execute($match, $request->user(), $request->validated(), allowUpdate: true)
        );
    }

    private function currentTournament(): Tournament
    {
        return Tournament::resolveCurrent();
    }
}
