<?php

namespace emmpaul\LaravelSpotify\Traits;

use Carbon\Carbon;

trait HasSpotifyAuth
{
    /**
     * Merge Spotify fields into model's $fillable.
     */
    public function initializeHasSpotifyAuth(): void
    {
        $this->mergeFillable([
            'spotify_id',
            'spotify_avatar',
            'spotify_token',
            'spotify_refresh_token',
            'spotify_token_expires_at',
        ]);
    }

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'spotify_token_expires_at' => 'datetime',
        ]);
    }

    public function hasSpotifyAuth(): bool
    {
        return ! empty($this->spotify_id) && ! empty($this->spotify_token);
    }

    public function isSpotifyTokenExpired(): bool
    {
        if (! $this->spotify_token_expires_at) {
            return false;
        }

        return Carbon::now()->isAfter($this->spotify_token_expires_at);
    }

    public function updateSpotifyTokens(string $accessToken, ?string $refreshToken = null, ?int $expiresIn = null): void
    {
        $this->update([
            'spotify_token' => $accessToken,
            'spotify_refresh_token' => $refreshToken ?? $this->spotify_refresh_token,
            'spotify_token_expires_at' => $expiresIn ? Carbon::now()->addSeconds($expiresIn) : null,
        ]);
    }

    public function clearSpotifyTokens(): void
    {
        $this->update([
            'spotify_token' => null,
            'spotify_refresh_token' => null,
            'spotify_token_expires_at' => null,
        ]);
    }
}
