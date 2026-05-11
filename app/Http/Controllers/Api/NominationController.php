<?php

namespace App\Http\Controllers\Api;

use App\Actions\SubmitNominationPredictionsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNominationPredictionsRequest;
use App\Http\Resources\NominationCategoryApiResource;
use App\Http\Resources\NominationPredictionResource;
use App\Models\NominationCategory;
use App\Models\NominationPrediction;
use App\Models\Tournament;
use App\Services\NominationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NominationController extends Controller
{
    public function index(NominationService $nominationService): AnonymousResourceCollection
    {
        $tournament = $this->currentTournament();

        return NominationCategoryApiResource::collection(
            NominationCategory::query()
                ->where('tournament_id', $tournament->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
        )->additional([
            'meta' => [
                'tournament_id' => $tournament->id,
                'lock_at' => $nominationService->firstMatchStartsAt($tournament)?->toIso8601String(),
                'is_locked' => $nominationService->predictionsAreLocked($tournament),
            ],
        ]);
    }

    public function store(StoreNominationPredictionsRequest $request, SubmitNominationPredictionsAction $action): AnonymousResourceCollection
    {
        return NominationPredictionResource::collection(
            $action->execute(
                $this->currentTournament(),
                $request->user(),
                $request->validated('predictions'),
            )
        );
    }

    public function mine(Request $request): AnonymousResourceCollection
    {
        return NominationPredictionResource::collection(
            NominationPrediction::query()
                ->with('nominationCategory')
                ->where('tournament_id', $this->currentTournament()->id)
                ->where('user_id', $request->user()->id)
                ->join('nomination_categories', 'nomination_predictions.nomination_category_id', '=', 'nomination_categories.id')
                ->orderBy('nomination_categories.sort_order')
                ->select('nomination_predictions.*')
                ->get()
        );
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
