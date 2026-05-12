<?php

namespace Database\Seeders;

use App\Models\NominationCategory;
use App\Models\Tournament;
use App\Services\NominationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class NominationCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(NominationService $nominationService): void
    {
        $this->tournaments()->each(function (Tournament $tournament) use ($nominationService): void {
            foreach ($nominationService->defaultCategories() as $key => $category) {
                NominationCategory::query()->updateOrCreate(
                    [
                        'tournament_id' => $tournament->id,
                        'key' => $key,
                    ],
                    $category,
                );
            }
        });
    }

    /**
     * @return Collection<int, Tournament>
     */
    private function tournaments(): Collection
    {
        $tournaments = Tournament::query()->get();

        if ($tournaments->isNotEmpty()) {
            return $tournaments;
        }

        return new Collection([
            Tournament::query()->create([
                'name' => 'World Cup 2026',
                'slug' => 'world-cup-2026',
                'status' => 'upcoming',
            ]),
        ]);
    }
}
