<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreNominationPredictionsRequest extends FormRequest
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
            'predictions' => ['required', 'array', 'min:1'],
            'predictions.*.category_key' => ['required', 'string'],
            'predictions.*.player_id' => ['nullable', 'integer', 'min:1'],
            'predictions.*.team_id' => ['nullable', 'integer', 'min:1'],
            'predictions.*.value_text' => ['nullable', 'string', 'max:255'],
            'predictions.*.value_number' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $keys = collect($this->input('predictions', []))
                    ->pluck('category_key')
                    ->filter();

                if ($keys->duplicates()->isNotEmpty()) {
                    $validator->errors()->add('predictions', 'Each nomination category may only be submitted once per request.');
                }
            },
        ];
    }
}
