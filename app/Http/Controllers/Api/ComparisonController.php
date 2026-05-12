<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ComparisonResource;
use App\Models\LeaderboardEntry;
use App\Models\MatchPrediction;
use App\Models\NominationCategory;
use App\Models\NominationPrediction;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ComparisonController extends Controller
{
    public function show(Request $request, User $user): ComparisonResource
    {
        $tournament = $this->currentTournament();
        $currentUser = $request->user();

        $participantIds = collect([$currentUser->id, $user->id])->unique()->values();

        $leaderboardEntries = LeaderboardEntry::query()
            ->with('user')
            ->where('tournament_id', $tournament->id)
            ->whereIn('user_id', $participantIds)
            ->get()
            ->keyBy('user_id');

        $matchPredictions = $this->matchPredictionsForUsers($tournament->id, $participantIds);
        $nominationPredictions = $this->nominationPredictionsForUsers($tournament->id, $participantIds);

        $matches = TournamentMatch::query()
            ->with(['homeTeam', 'awayTeam'])
            ->where('tournament_id', $tournament->id)
            ->orderBy('starts_at')
            ->orderBy('match_number')
            ->get()
            ->map(function (TournamentMatch $match) use ($currentUser, $user, $matchPredictions, $tournament): array {
                return [
                    'id' => $match->id,
                    'stage' => $match->stage,
                    'group_name' => $match->group_name,
                    'status' => $match->status,
                    'starts_at' => $match->starts_at->toIso8601String(),
                    'lock_at' => $match->starts_at->copy()->subMinutes($tournament->prediction_lock_minutes)->toIso8601String(),
                    'home_team' => [
                        'id' => $match->home_team_id,
                        'name' => $match->homeTeam?->name ?? $match->home_placeholder,
                    ],
                    'away_team' => [
                        'id' => $match->away_team_id,
                        'name' => $match->awayTeam?->name ?? $match->away_placeholder,
                    ],
                    'result' => $match->status === 'finished' ? [
                        'home_score' => $match->home_score,
                        'away_score' => $match->away_score,
                        'home_penalty_score' => $match->home_penalty_score,
                        'away_penalty_score' => $match->away_penalty_score,
                    ] : null,
                    'me_prediction' => $this->predictionData($matchPredictions->get($currentUser->id)?->get($match->id)),
                    'opponent_prediction' => $this->predictionData($matchPredictions->get($user->id)?->get($match->id)),
                ];
            })
            ->filter(fn (array $row): bool => $row['me_prediction'] !== null || $row['opponent_prediction'] !== null)
            ->values();

        $nominations = NominationCategory::query()
            ->where('tournament_id', $tournament->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (NominationCategory $category) use ($currentUser, $user, $nominationPredictions): array {
                return [
                    'id' => $category->id,
                    'key' => $category->key,
                    'name' => $category->name,
                    'type' => $category->type,
                    'points' => $category->points,
                    'me_prediction' => $this->nominationPredictionData($nominationPredictions->get($currentUser->id)?->get($category->id)),
                    'opponent_prediction' => $this->nominationPredictionData($nominationPredictions->get($user->id)?->get($category->id)),
                ];
            })
            ->filter(fn (array $row): bool => $row['me_prediction'] !== null || $row['opponent_prediction'] !== null)
            ->values();

        return ComparisonResource::make([
            'tournament' => [
                'id' => $tournament->id,
                'name' => $tournament->name,
                'status' => $tournament->status,
            ],
            'me' => $this->participantData($currentUser, $leaderboardEntries->get($currentUser->id)),
            'opponent' => $this->participantData($user, $leaderboardEntries->get($user->id)),
            'matches' => $matches,
            'nominations' => $nominations,
        ]);
    }

    private function participantData(User $user, ?LeaderboardEntry $entry): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'rank' => $entry?->rank,
            'match_points' => $entry?->match_points ?? 0,
            'nomination_points' => $entry?->nomination_points ?? 0,
            'total_points' => $entry?->total_points ?? 0,
            'exact_scores_count' => $entry?->exact_scores_count ?? 0,
            'goal_difference_count' => $entry?->goal_difference_count ?? 0,
            'result_count' => $entry?->result_count ?? 0,
        ];
    }

    /**
     * @param  Collection<int, int>  $participantIds
     * @return Collection<int, Collection<int, MatchPrediction>>
     */
    private function matchPredictionsForUsers(int $tournamentId, Collection $participantIds): Collection
    {
        return MatchPrediction::query()
            ->with(['tournamentMatch.homeTeam', 'tournamentMatch.awayTeam'])
            ->whereIn('user_id', $participantIds)
            ->whereHas('tournamentMatch', fn (Builder $query) => $query->where('tournament_id', $tournamentId))
            ->join('tournament_matches', 'match_predictions.tournament_match_id', '=', 'tournament_matches.id')
            ->orderBy('tournament_matches.starts_at')
            ->select('match_predictions.*')
            ->get()
            ->groupBy('user_id')
            ->map(fn (Collection $predictions): Collection => $predictions->keyBy('tournament_match_id'));
    }

    /**
     * @param  Collection<int, int>  $participantIds
     * @return Collection<int, Collection<int, NominationPrediction>>
     */
    private function nominationPredictionsForUsers(int $tournamentId, Collection $participantIds): Collection
    {
        return NominationPrediction::query()
            ->with('nominationCategory')
            ->where('nomination_predictions.tournament_id', $tournamentId)
            ->whereIn('nomination_predictions.user_id', $participantIds)
            ->join('nomination_categories', 'nomination_predictions.nomination_category_id', '=', 'nomination_categories.id')
            ->orderBy('nomination_categories.sort_order')
            ->select('nomination_predictions.*')
            ->get()
            ->groupBy('user_id')
            ->map(fn (Collection $predictions): Collection => $predictions->keyBy('nomination_category_id'));
    }

    private function predictionData(?MatchPrediction $prediction): ?array
    {
        if (! $prediction) {
            return null;
        }

        return [
            'id' => $prediction->id,
            'home_score' => $prediction->home_score,
            'away_score' => $prediction->away_score,
            'home_penalty_score' => $prediction->home_penalty_score,
            'away_penalty_score' => $prediction->away_penalty_score,
            'submitted_at' => $prediction->submitted_at?->toIso8601String(),
            'calculated_at' => $prediction->calculated_at?->toIso8601String(),
            'points' => $prediction->calculated_at !== null ? [
                'match_points' => $prediction->match_points,
                'penalty_points' => $prediction->penalty_points,
                'total_points' => $prediction->total_points,
            ] : null,
        ];
    }

    private function nominationPredictionData(?NominationPrediction $prediction): ?array
    {
        if (! $prediction) {
            return null;
        }

        return [
            'id' => $prediction->id,
            'value_text' => $prediction->value_text,
            'value_number' => $prediction->value_number,
            'points' => $prediction->points,
            'calculated_at' => $prediction->calculated_at?->toIso8601String(),
        ];
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
