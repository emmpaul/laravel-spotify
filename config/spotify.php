<?php

return [
    'api_base_url' => env('SPOTIFY_API_BASE_URL', 'https://api.spotify.com/v1'),

    'redirect_route_after_login' => '/dashboard',

    'client_id' => env('SPOTIFY_CLIENT_ID'),
    'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
    'redirect' => env('SPOTIFY_REDIRECT_URI'),
    'scopes' => [
        // add scopes here
    ],
];
