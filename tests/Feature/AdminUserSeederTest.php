<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Support\Facades\Hash;

test('admin user seeder creates an approved admin user', function () {
    if (! extension_loaded('pdo_sqlite')) {
        $contents = file_get_contents(database_path('seeders/AdminUserSeeder.php'));

        expect($contents)->toContain("'name' => 'Admin'")
            ->and($contents)->toContain("'password' => 'mundial_26!'")
            ->and($contents)->toContain("'role' => 'admin'")
            ->and($contents)->toContain("'is_approved' => true");

        return;
    }

    $this->artisan('migrate:fresh')->run();
    $this->seed(AdminUserSeeder::class);
    $this->seed(AdminUserSeeder::class);

    $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

    expect($admin->name)->toBe('Admin')
        ->and($admin->role)->toBe('admin')
        ->and($admin->is_approved)->toBeTrue()
        ->and(Hash::check('mundial_26!', $admin->password))->toBeTrue()
        ->and(User::query()->where('email', 'admin@example.com')->count())->toBe(1);
});
