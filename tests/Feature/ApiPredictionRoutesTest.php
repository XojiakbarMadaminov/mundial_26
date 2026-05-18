<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\HasApiTokens;

test('prediction api routes are registered', function (string $method, string $uri, ?string $routeUri = null) {
    $route = Route::getRoutes()->match(request: request()->create($uri, $method));

    expect($route->uri())->toBe($routeUri ?? ltrim($uri, '/'));
})->with([
    'current tournament' => ['GET', '/api/tournaments/current', null],
    'matches' => ['GET', '/api/matches', null],
    'today matches' => ['GET', '/api/matches/today', null],
    'match show' => ['GET', '/api/matches/1', 'api/matches/{match}'],
    'store prediction' => ['POST', '/api/matches/1/prediction', 'api/matches/{match}/prediction'],
    'update prediction' => ['PUT', '/api/matches/1/prediction', 'api/matches/{match}/prediction'],
    'my predictions' => ['GET', '/api/my-predictions', null],
    'leaderboard' => ['GET', '/api/leaderboard', null],
    'comparison' => ['GET', '/api/comparison/1', 'api/comparison/{user}'],
    'nominations' => ['GET', '/api/nominations', null],
    'nomination player options' => ['GET', '/api/nomination-options/players', null],
    'nomination team options' => ['GET', '/api/nomination-options/teams', null],
    'store nomination predictions' => ['POST', '/api/nominations/predictions', null],
    'my nomination predictions' => ['GET', '/api/my-nomination-predictions', null],
]);

test('prediction write routes use sanctum authentication', function (string $method, string $uri) {
    $route = Route::getRoutes()->match(request: request()->create($uri, $method));

    expect($route->gatherMiddleware())->toContain('auth:sanctum');
})->with([
    'store prediction' => ['POST', '/api/matches/1/prediction'],
    'update prediction' => ['PUT', '/api/matches/1/prediction'],
    'my predictions' => ['GET', '/api/my-predictions'],
    'comparison' => ['GET', '/api/comparison/1'],
    'store nomination predictions' => ['POST', '/api/nominations/predictions'],
    'my nomination predictions' => ['GET', '/api/my-nomination-predictions'],
]);

test('user model supports sanctum api tokens', function () {
    expect(class_uses_recursive(User::class))->toContain(HasApiTokens::class);
});
