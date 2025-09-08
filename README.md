# Laravel Spotify Authentication & API Wrapper

A comprehensive Laravel package that provides Spotify OAuth authentication and a complete wrapper around the Spotify Web API.

## Features

- 🔐 **OAuth Authentication**: Seamless Spotify OAuth integration with user management
- 🎵 **Complete API Wrapper**: Full access to Spotify Web API endpoints
- 👤 **User Integration**: Trait-based user model extension with token management
- 🎯 **Type Safety**: Enums for time ranges and data types

[//]: # ([![Latest Version on Packagist]&#40;https://img.shields.io/packagist/v/emmpaul/laravel-spotify.svg?style=flat-square&#41;]&#40;https://packagist.org/packages/emmpaul/laravel-spotify&#41;)

[//]: # ([![GitHub Tests Action Status]&#40;https://img.shields.io/github/actions/workflow/status/emmpaul/laravel-spotify/run-tests.yml?branch=main&label=tests&style=flat-square&#41;]&#40;https://github.com/emmpaul/laravel-spotify/actions?query=workflow%3Arun-tests+branch%3Amain&#41;)

[//]: # ([![GitHub Code Style Action Status]&#40;https://img.shields.io/github/actions/workflow/status/emmpaul/laravel-spotify/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square&#41;]&#40;https://github.com/emmpaul/laravel-spotify/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain&#41;)

[//]: # ([![Total Downloads]&#40;https://img.shields.io/packagist/dt/emmpaul/laravel-spotify.svg?style=flat-square&#41;]&#40;https://packagist.org/packages/emmpaul/laravel-spotify&#41;)

## Installation

You can install the package via composer:

```bash
composer require emmpaul/laravel-spotify
```

You can publish the config and migrations with:

```bash
php artisan vendor:publish --provider="emmpaul\LaravelSpotify\LaravelSpotifyServiceProvider"
```

Then run the migrations:

```bash
php artisan migrate
```

## Environment Variables

Add the following environment variables to your `.env` file:

```env
SPOTIFY_CLIENT_ID=your_spotify_client_id
SPOTIFY_CLIENT_SECRET=your_spotify_client_secret
SPOTIFY_REDIRECT_URI=
```
> SPOTIFY_REDIRECT_URI needs to match the redirect URI you set in your Spotify app. 

This is the contents of the published config file:

```php
return [
    'api_base_url' => env('SPOTIFY_API_BASE_URL', 'https://api.spotify.com/v1'),
    'redirect_route_after_login' => '/dashboard',
    'client_id' => env('SPOTIFY_CLIENT_ID'),
    'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
    'redirect' => env('SPOTIFY_REDIRECT_URI'),
    'scopes' => [
        // Add your required scopes here
    ],
];
```

> **Note**: Customize the `redirect_route_after_login` and `scopes` to match your application needs.

## Usage

### User Model Setup

Add the `HasSpotifyAuth` trait to your User model:

```php
// App\Models\User
use emmpaul\LaravelSpotify\Traits\HasSpotifyAuth;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasSpotifyAuth;
    
    // Your existing model code...
}
```

### Authentication Routes

The package automatically registers the following routes:

```php
// Redirect to Spotify OAuth
Route::get('/auth/spotify', [SpotifyAuthController::class, 'redirect'])->name('spotify.auth');

// Handle OAuth callback
Route::get('/auth/callback', [SpotifyAuthController::class, 'callback'])->name('spotify.callback');
```

### Basic Authentication Flow

In your view or controller:

```php
// Generate Spotify login URL
use emmpaul\LaravelSpotify\Facades\LaravelSpotify;

$spotifyAuthUrl = LaravelSpotify::getAuthUrl();
```

Or use the named route:

```php
<a href="{{ route('spotify.auth') }}">Login with Spotify</a>
```

### User Methods

The `HasSpotifyAuth` trait provides several helpful methods:

```php
// Check if user has Spotify authentication
$user->hasSpotifyAuth(); // Returns boolean

// Check if token is expired
$user->isSpotifyTokenExpired(); // Returns boolean

// Update tokens (typically done automatically)
$user->updateSpotifyTokens($accessToken, $refreshToken, $expiresIn);

// Clear tokens
$user->clearSpotifyTokens();
```

### Using the Spotify API

#### Via Facade

```php
use emmpaul\LaravelSpotify\Facades\LaravelSpotify;

// Set access token
$spotify = LaravelSpotify::setAccessToken($user->spotify_token);

// Get user profile
$profile = $spotify->api()->getCurrentUsersProfile();

// Get user's top tracks
$topTracks = $spotify->api()->getUserTopTracks();

// Get user's playlists
$playlists = $spotify->api()->getCurrentUsersPlaylists();
```

#### Via Service Class

```php
use emmpaul\LaravelSpotify\Services\SpotifyService;

$spotifyService = new SpotifyService($user->spotify_token);

// Get current user's profile
$response = $spotifyService->getCurrentUsersProfile();
$userData = $response->json();

// Search for tracks
$results = $spotifyService->searchForItem('Bohemian Rhapsody', ['track']);

// Get a specific track
$track = $spotifyService->getTrack('4u7EnebtmKWzUH433cf1Qv');
```

#### Using with Authenticated User

```php
use emmpaul\LaravelSpotify\Facades\LaravelSpotify;
use Illuminate\Support\Facades\Auth;

// In a controller method
public function getUserStats()
{
    $user = Auth::user();
    
    if (!$user->hasSpotifyAuth()) {
        return redirect()->route('spotify.auth');
    }
    
    $spotify = LaravelSpotify::setAccessToken($user->spotify_token);
    
    // Get user's top artists (last 6 months)
    $topArtists = $spotify->api()->getUserTopArtists('medium_term', 10);
    
    // Get recently played tracks
    $recentTracks = $spotify->api()->getRecentlyPlayedTracks(20);
    
    return view('spotify.stats', [
        'topArtists' => $topArtists->json(),
        'recentTracks' => $recentTracks->json(),
    ]);
}
```

### Available API Methods

#### Albums
```php
$spotify->api()->getAlbum($albumId);
$spotify->api()->getAlbums([$albumId1, $albumId2]);
$spotify->api()->getAlbumTracks($albumId);
$spotify->api()->getUserSavedAlbums();
$spotify->api()->getNewReleases();
```

#### Artists
```php
$spotify->api()->getArtist($artistId);
$spotify->api()->getSeveralArtists([$artistId1, $artistId2]);
$spotify->api()->getArtistsAlbums($artistId);
$spotify->api()->getArtistsTopTracks($artistId);
```

#### Tracks
```php
$spotify->api()->getTrack($trackId);
$spotify->api()->getSeveralTracks([$trackId1, $trackId2]);
$spotify->api()->getUsersSavedTracks();
```

#### Playlists
```php
$spotify->api()->getPlaylist($playlistId);
$spotify->api()->getPlaylistItems($playlistId);
$spotify->api()->getCurrentUsersPlaylists();
$spotify->api()->getUsersPlaylists($userId);
```

#### User Data
```php
use emmpaul\LaravelSpotify\Enums\SpotifyTimeRange;
use emmpaul\LaravelSpotify\Enums\SpotifyTopType;

// Get top items with enums
$spotify->api()->getUserTop(SpotifyTopType::TRACKS, SpotifyTimeRange::SHORT_TERM, 20);
$spotify->api()->getUserTop(SpotifyTopType::ARTISTS, SpotifyTimeRange::LONG_TERM, 50);

// Convenience methods
$spotify->api()->getUserTopTracks('short_term', 20);
$spotify->api()->getUserTopArtists('long_term', 50);

// Recently played
$spotify->api()->getRecentlyPlayedTracks(50);
```

#### Player/Playback
```php
$spotify->api()->getPlaybackState();
$spotify->api()->getCurrentlyPlayingTrack();
$spotify->api()->getAvailableDevices();
$spotify->api()->getTheUsersQueue();
```

#### Search
```php
// Search for multiple types
$results = $spotify->api()->searchForItem('Queen', ['artist', 'album', 'track']);

// Search with market and limit
$results = $spotify->api()->searchForItem('Bohemian Rhapsody', ['track'], 'US', 10);
```

### Error Handling

```php
try {
    $response = $spotify->api()->getCurrentUsersProfile();
    
    if ($response->successful()) {
        $userData = $response->json();
        // Handle successful response
    } else {
        // Handle API errors
        $error = $response->json();
        Log::error('Spotify API Error: ' . $response->status(), $error);
    }
} catch (\RuntimeException $e) {
    // Handle missing access token
    return redirect()->route('spotify.auth');
} catch (\Exception $e) {
    // Handle other exceptions
    Log::error('Spotify Error: ' . $e->getMessage());
}
```

### Time Ranges

Use the `SpotifyTimeRange` enum for top tracks/artists:

```php
use emmpaul\LaravelSpotify\Enums\SpotifyTimeRange;

// Available time ranges:
SpotifyTimeRange::SHORT_TERM;  // ~4 weeks
SpotifyTimeRange::MEDIUM_TERM; // ~6 months (default)
SpotifyTimeRange::LONG_TERM;   // Several years

// Usage
$topTracks = $spotify->api()->getUserTopTracks(SpotifyTimeRange::SHORT_TERM, 20);
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Emmanuel Paul](https://github.com/emmpaul)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
