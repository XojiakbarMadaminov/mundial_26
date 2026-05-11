<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\PredictionController;
use App\Http\Controllers\Api\TournamentController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::get('/tournaments/current', [TournamentController::class, 'current']);
Route::get('/matches', [MatchController::class, 'index']);
Route::get('/matches/today', [MatchController::class, 'today']);
Route::get('/matches/{match}', [MatchController::class, 'show']);
Route::get('/leaderboard', [LeaderboardController::class, 'index']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/matches/{match}/prediction', [PredictionController::class, 'store']);
    Route::put('/matches/{match}/prediction', [PredictionController::class, 'update']);
    Route::get('/my-predictions', [PredictionController::class, 'index']);
});
