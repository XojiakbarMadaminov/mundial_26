<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin-mundial@gmail.com'],
            [
                'name' => 'Admin',
                'password' => 'mundial_26!',
                'role' => 'admin',
                'is_approved' => true,
            ],
        );
    }
}
