<?php

namespace App\Http\Requests;

use App\Models\TournamentMatch;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePredictionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'home_score' => ['required', 'integer', 'min:0', 'max:30'],
            'away_score' => ['required', 'integer', 'min:0', 'max:30'],
            'home_penalty_score' => ['nullable', 'integer', 'min:0', 'max:30'],
            'away_penalty_score' => ['nullable', 'integer', 'min:0', 'max:30'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $match = $this->route('match');

                if (! $match instanceof TournamentMatch) {
                    return;
                }

                $match->loadMissing('tournament');

                if (now()->gte($match->starts_at->copy()->subMinutes($match->tournament->prediction_lock_minutes))) {
                    $validator->errors()->add('match', 'Prediction is locked for this match.');
                }

                if ($this->hasPenaltyPrediction() && ! in_array($match->stage, $this->playoffStages(), true)) {
                    $validator->errors()->add('home_penalty_score', 'Penalty prediction is only allowed for playoff matches.');
                }
            },
        ];
    }

    private function hasPenaltyPrediction(): bool
    {
        return $this->filled('home_penalty_score') || $this->filled('away_penalty_score');
    }

    /**
     * @return array<int, string>
     */
    private function playoffStages(): array
    {
        return ['round_32', 'round_16', 'quarter_final', 'semi_final', 'third_place', 'final'];
    }
}
