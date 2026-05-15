<?php

test('nomination frontend uses searchable selects for player and team nominations', function () {
    $contents = file_get_contents(resource_path('js/spa/pages/NominationsPage.vue'));

    expect($contents)->toContain('role="combobox"')
        ->and($contents)->toContain('role="listbox"')
        ->and($contents)->toContain('selectOption(category, option)')
        ->and($contents)->toContain('NominationOptionController.players')
        ->and($contents)->toContain('NominationOptionController.teams')
        ->and($contents)->not->toContain('<select');
});
