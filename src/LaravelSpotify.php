<?php

namespace emmpaul\LaravelSpotify;

use emmpaul\LaravelSpotify\Services\SpotifyService;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

class LaravelSpotify
{
    protected ?string $accessToken = null;

    protected ?SpotifyService $spotify = null;

    public function __construct(?string $accessToken = null)
    {
        if ($accessToken) {
            $this->setAccessToken($accessToken);
        }
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;
        $this->spotify = new SpotifyService($accessToken);

        return $this;
    }

    public function getAuthUrl(): string
    {
        $driver = Socialite::driver('spotify');

        if ($driver instanceof AbstractProvider) {
            $scopes = config('spotify.scopes', []);
            if (! empty($scopes)) {
                $driver = $driver->scopes($scopes);
            }
        }

        return $driver->redirect()->getTargetUrl();
    }

    public function api(): SpotifyService
    {
        if (! $this->spotify) {
            throw new \RuntimeException('Access token not set. Call setAccessToken() first.');
        }

        return $this->spotify;
    }

    public static function make(?string $accessToken = null): self
    {
        return new self($accessToken);
    }
}
