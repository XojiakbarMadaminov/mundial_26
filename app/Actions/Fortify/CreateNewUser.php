<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'telegram_username' => ['nullable', 'string', 'max:255', 'unique:users,telegram_username', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:255', 'unique:users,phone', 'required_without:telegram_username'],
            'password' => $this->passwordRules(),
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'telegram_username' => $input['telegram_username'] ?? null,
            'phone' => $input['phone'] ?? null,
            'password' => $input['password'],
            'is_approved' => false,
        ]);
    }
}
