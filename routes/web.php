<?php

use App\Http\Controllers\AdminLocaleController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'frontend')->name('home');
Route::view('/dashboard', 'frontend')->name('dashboard');
Route::view('/matches', 'frontend')->name('matches.index');
Route::view('/matches/{match}', 'frontend')->whereNumber('match')->name('matches.show');
Route::view('/predictions', 'frontend')->name('predictions.index');
Route::view('/nominations', 'frontend')->name('nominations.index');
Route::view('/leaderboard', 'frontend')->name('leaderboard.index');
Route::view('/comparison', 'frontend')->name('comparison');
Route::view('/rules', 'frontend')->name('rules');

Route::get('/admin/locale/{locale}', AdminLocaleController::class)
    ->whereIn('locale', ['en', 'uz'])
    ->name('admin.locale');

require __DIR__.'/settings.php';
