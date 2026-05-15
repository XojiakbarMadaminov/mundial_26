<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class NominationOptionController extends Controller
{
    /**
     * @return array{data: Collection<int, array{id: int, name: string, team_name: string|null}>}
     */
    public function players(Request $request): array
    {
        return [
            'data' => Player::query()
                ->select(['id', 'name', 'team_id'])
                ->with('team:id,name')
                ->where('tournament_id', $this->currentTournament()->id)
                ->when($request->string('search')->trim()->isNotEmpty(), function ($query) use ($request): void {
                    $query->where('name', 'like', '%'.$request->string('search')->trim().'%');
                })
                ->orderBy('name')
                ->limit(50)
                ->get()
                ->map(fn (Player $player): array => [
                    'id' => $player->id,
                    'name' => $player->name,
                    'team_name' => $player->team?->name,
                ]),
        ];
    }

    /**
     * @return array{data: Collection<int, array{id: int, name: string, code: string|null}>}
     */
    public function teams(Request $request): array
    {
        return [
            'data' => Team::query()
                ->select(['id', 'name', 'code'])
                ->where('tournament_id', $this->currentTournament()->id)
                ->when($request->string('search')->trim()->isNotEmpty(), function ($query) use ($request): void {
                    $query->where(function ($query) use ($request): void {
                        $search = '%'.$request->string('search')->trim().'%';

                        $query->where('name', 'like', $search)
                            ->orWhere('code', 'like', $search);
                    });
                })
                ->orderBy('name')
                ->limit(50)
                ->get()
                ->map(fn (Team $team): array => [
                    'id' => $team->id,
                    'name' => $team->name,
                    'code' => $team->code,
                ]),
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
