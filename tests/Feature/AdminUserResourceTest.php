<?php

test('admin users resource exposes telegram username on list and show pages', function () {
    $resource = file_get_contents(app_path('Filament/Resources/Users/UserResource.php'));
    $table = file_get_contents(app_path('Filament/Resources/Users/Tables/UsersTable.php'));

    expect($table)->toContain("TextColumn::make('telegram_username')")
        ->and($table)->toContain('ViewAction::make()')
        ->and($resource)->toContain("TextEntry::make('telegram_username')")
        ->and($resource)->toContain("'view' => ViewUser::route('/{record}')");
});
