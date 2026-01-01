<?php

namespace emmpaul\LaravelSpotify\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SpotifyAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        /** @var \Laravel\Socialite\Two\SpotifyProvider $driver */
        $driver = Socialite::driver('spotify');

        return $driver
            ->scopes(config('spotify.scopes', []))
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $spotifyUser = Socialite::driver('spotify')->user();

            $user = config('auth.providers.users.model')::query()->firstOrCreate([
                'email' => $spotifyUser->getEmail(),
            ], [
                'name' => $spotifyUser->getName() ?: $spotifyUser->getNickname(),
                'email' => $spotifyUser->getEmail(),
                'password' => Hash::make(uniqid()),
            ]);

            $user->update([
                'spotify_id' => $spotifyUser->getId(),
                'spotify_avatar' => $spotifyUser->getAvatar(),
                'spotify_token' => $spotifyUser->token ?? null,
                'spotify_refresh_token' => $spotifyUser->refreshToken ?? null,
            ]);

            Auth::login($user);

            return redirect()->intended(config('spotify.redirect_route_after_login'));
        } catch (\Exception $e) {
            Log::error('Spotify authentication failed: ' . $e->getMessage());
            return redirect('/login')->withErrors([
                'spotify' => 'Authentication with Spotify failed',
            ]);
        }
    }
}
