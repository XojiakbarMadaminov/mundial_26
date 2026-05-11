<?php

namespace App\Actions;

use App\Models\NominationCategory;
use App\Models\NominationPrediction;
use App\Models\Tournament;
use App\Models\User;
use App\Services\NominationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmitNominationPredictionsAction
{
    public function __construct(
        private readonly NominationService $nominationService,
    ) {}

    /**
     * @param  array<int, array{category_key: string, value_text?: string|null, value_number?: int|null}>  $predictions
     * @return Collection<int, NominationPrediction>
     */
    public function execute(Tournament $tournament, User $user, array $predictions): Collection
    {
        if ($this->nominationService->predictionsAreLocked($tournament)) {
            throw ValidationException::withMessages([
                'tournament' => 'Nomination predictions are locked.',
            ]);
        }

        return DB::transaction(function () use ($tournament, $user, $predictions): Collection {
            $categories = NominationCategory::query()
                ->where('tournament_id', $tournament->id)
                ->whereIn('key', collect($predictions)->pluck('category_key')->all())
                ->get()
                ->keyBy('key');

            return collect($predictions)->map(function (array $prediction) use ($categories, $tournament, $user): NominationPrediction {
                $category = $categories->get($prediction['category_key']);

                if (! $category) {
                    throw ValidationException::withMessages([
                        'predictions' => "Unknown nomination category [{$prediction['category_key']}].",
                    ]);
                }

                $payload = $this->payloadForCategory($category, $prediction);

                return NominationPrediction::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'nomination_category_id' => $category->id,
                    ],
                    [
                        'tournament_id' => $tournament->id,
                        ...$payload,
                        'points' => 0,
                        'calculated_at' => null,
                    ],
                )->load('nominationCategory');
            })->values();
        });
    }

    /**
     * @param  array{category_key: string, value_text?: string|null, value_number?: int|null}  $prediction
     * @return array{value_text: string|null, value_number: int|null}
     */
    private function payloadForCategory(NominationCategory $category, array $prediction): array
    {
        if ($category->type === 'number') {
            if (! array_key_exists('value_number', $prediction) || $prediction['value_number'] === null) {
                throw ValidationException::withMessages([
                    'predictions' => "A numeric value is required for [{$category->key}].",
                ]);
            }

            return [
                'value_text' => null,
                'value_number' => (int) $prediction['value_number'],
            ];
        }

        if (! filled($prediction['value_text'] ?? null)) {
            throw ValidationException::withMessages([
                'predictions' => "A text value is required for [{$category->key}].",
            ]);
        }

        return [
            'value_text' => $this->nominationService->normalizeText((string) $prediction['value_text']),
            'value_number' => null,
        ];
    }
}
