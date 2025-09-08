<?php

namespace emmpaul\LaravelSpotify\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \emmpaul\LaravelSpotify\LaravelSpotify setAccessToken(string $accessToken)
 * @method static \emmpaul\LaravelSpotify\Services\SpotifyService api()
 * @method static string getAuthUrl()
 * @method static \emmpaul\LaravelSpotify\LaravelSpotify make(?string $accessToken = null)
 *
 * @see \emmpaul\LaravelSpotify\LaravelSpotify
 */
class LaravelSpotify extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \emmpaul\LaravelSpotify\LaravelSpotify::class;
    }
}
