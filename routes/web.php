<?php

use emmpaul\LaravelSpotify\Controllers\SpotifyAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/auth/spotify', [SpotifyAuthController::class, 'redirect'])->name('spotify.auth');
    Route::get('/auth/callback', [SpotifyAuthController::class, 'callback'])->name('spotify.callback');
});
