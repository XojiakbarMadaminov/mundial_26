<?php

use App\Services\NominationService;

test('default nomination categories match competition scoring rules', function () {
    $categories = app(NominationService::class)->defaultCategories();

    expect($categories)->toHaveKeys([
        'top_scorer',
        'top_scorer_goals',
        'best_player',
        'best_goalkeeper',
        'goalkeeper_conceded_goals',
        'champion',
        'worst_team',
    ])
        ->and($categories)->sequence(
            fn ($category) => $category
                ->toMatchArray(['name' => "To'purar", 'type' => 'player', 'points' => 30, 'sort_order' => 10]),
            fn ($category) => $category
                ->toMatchArray(['name' => "To'purar urgan gollari", 'type' => 'number', 'points' => 30, 'sort_order' => 20]),
            fn ($category) => $category
                ->toMatchArray(['name' => 'Eng yaxshi futbolchi', 'type' => 'player', 'points' => 30, 'sort_order' => 30]),
            fn ($category) => $category
                ->toMatchArray(['name' => 'Eng yaxshi darvozabon', 'type' => 'player', 'points' => 30, 'sort_order' => 40]),
            fn ($category) => $category
                ->toMatchArray(['name' => "Eng yaxshi darvozabon o'tkazgan gollari", 'type' => 'number', 'points' => 30, 'sort_order' => 50]),
            fn ($category) => $category
                ->toMatchArray(['name' => 'Champion', 'type' => 'team', 'points' => 30, 'sort_order' => 60]),
            fn ($category) => $category
                ->toMatchArray(['name' => 'Muvaffaqiyatsiz jamoa', 'type' => 'team', 'points' => 30, 'sort_order' => 70]),
        );
});
