<?php

namespace App\Actions;

use App\Models\NominationPrediction;
use App\Models\Tournament;
use App\Services\NominationService;
use Illuminate\Support\Facades\DB;

class RecalculateNominationPointsAction
{
    public function __construct(
        private readonly NominationService $nominationService,
        private readonly RecalculateLeaderboardAction $recalculateLeaderboard,
    ) {}

    public function execute(Tournament|int $tournament): void
    {
        $tournamentId = $tournament instanceof Tournament ? $tournament->id : $tournament;

        DB::transaction(function () use ($tournamentId): void {
            NominationPrediction::query()
                ->where('tournament_id', $tournamentId)
                ->update([
                    'points' => 0,
                    'calculated_at' => null,
                ]);

            NominationPrediction::query()
                ->with(['nominationCategory.result'])
                ->where('tournament_id', $tournamentId)
                ->each(function (NominationPrediction $prediction): void {
                    $prediction->update([
                        'points' => $this->nominationService->pointsFor(
                            $prediction->nominationCategory,
                            $prediction,
                            $prediction->nominationCategory->result,
                        ),
                        'calculated_at' => now(),
                    ]);
                });
        });

        $this->recalculateLeaderboard->execute($tournamentId);
    }
}
