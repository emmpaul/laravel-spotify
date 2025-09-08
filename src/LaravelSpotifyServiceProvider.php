<?php

namespace emmpaul\LaravelSpotify;

use emmpaul\LaravelSpotify\Commands\LaravelSpotifyCommand;
use Illuminate\Support\Facades\Event;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Spotify\SpotifyExtendSocialite;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelSpotifyServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-spotify')
            ->hasConfigFile()
            ->hasRoutes('web')
            ->hasMigration('add_spotify_fields_to_users_table');
        // ->hasCommand(LaravelSpotifyCommand::class);
    }

    public function bootingPackage(): void
    {
        Event::listen(SocialiteWasCalled::class, SpotifyExtendSocialite::class);
    }

    public function registeringPackage(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/spotify.php', 'services.spotify');
    }
}
