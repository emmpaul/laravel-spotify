<?php

use emmpaul\LaravelSpotify\Services\SpotifyService;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * @property SpotifyService $service
 */
beforeEach(function () {
    $this->service = new SpotifyService('test-access-token');
});

describe('getUserTopTracks', function () {
    it('calls the correct endpoint with default parameters', function () {
        Http::fake([
            'api.spotify.com/v1/me/top/tracks*' => Http::response([
                'items' => [],
                'total' => 0,
                'limit' => 20,
                'offset' => 0,
            ], 200),
        ]);

        $response = $this->service->getUserTopTracks();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.spotify.com/v1/me/top/tracks?limit=20&time_range=medium_term' &&
                   $request->hasHeader('Authorization', 'Bearer test-access-token') &&
                   $request->hasHeader('Content-Type', 'application/json');
        });

        expect($response)->toBeInstanceOf(Response::class);
    });

    it('accepts custom time range parameter', function () {
        Http::fake([
            'api.spotify.com/v1/me/top/tracks*' => Http::response([
                'items' => [],
                'total' => 0,
                'limit' => 20,
                'offset' => 0,
            ], 200),
        ]);

        $this->service->getUserTopTracks('long_term');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'time_range=long_term');
        });
    });

    it('accepts custom limit parameter', function () {
        Http::fake([
            'api.spotify.com/v1/me/top/tracks*' => Http::response([
                'items' => [],
                'total' => 0,
                'limit' => 50,
                'offset' => 0,
            ], 200),
        ]);

        $this->service->getUserTopTracks('medium_term', 50);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'limit=50');
        });
    });

    it('accepts both custom time range and limit', function () {
        Http::fake([
            'api.spotify.com/v1/me/top/tracks*' => Http::response([
                'items' => [],
                'total' => 0,
                'limit' => 10,
                'offset' => 0,
            ], 200),
        ]);

        $this->service->getUserTopTracks('short_term', 10);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'time_range=short_term') &&
                   str_contains($request->url(), 'limit=10');
        });
    });

    it('throws exception when no access token is set', function () {
        $service = new SpotifyService;

        expect(fn () => $service->getUserTopTracks())
            ->toThrow(RuntimeException::class, 'Access token is required');
    });

    it('returns response with tracks data', function () {
        $tracksData = [
            'items' => [
                [
                    'id' => 'track1',
                    'name' => 'Test Track 1',
                    'artists' => [['name' => 'Test Artist 1']],
                    'album' => ['name' => 'Test Album 1'],
                ],
                [
                    'id' => 'track2',
                    'name' => 'Test Track 2',
                    'artists' => [['name' => 'Test Artist 2']],
                    'album' => ['name' => 'Test Album 2'],
                ],
            ],
            'total' => 2,
            'limit' => 20,
            'offset' => 0,
        ];

        Http::fake([
            'api.spotify.com/v1/me/top/tracks*' => Http::response($tracksData, 200),
        ]);

        $response = $this->service->getUserTopTracks();

        expect($response->json())->toEqual($tracksData);
    });
});

describe('getUserTopArtists', function () {
    it('calls the correct endpoint with default parameters', function () {
        Http::fake([
            'api.spotify.com/v1/me/top/artists*' => Http::response([
                'items' => [],
                'total' => 0,
                'limit' => 20,
                'offset' => 0,
            ], 200),
        ]);

        $response = $this->service->getUserTopArtists();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.spotify.com/v1/me/top/artists?limit=20&time_range=medium_term' &&
                   $request->hasHeader('Authorization', 'Bearer test-access-token') &&
                   $request->hasHeader('Content-Type', 'application/json');
        });

        expect($response)->toBeInstanceOf(Response::class);
    });

    it('accepts custom time range parameter', function () {
        Http::fake([
            'api.spotify.com/v1/me/top/artists*' => Http::response([
                'items' => [],
                'total' => 0,
                'limit' => 20,
                'offset' => 0,
            ], 200),
        ]);

        $this->service->getUserTopArtists('long_term');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'time_range=long_term');
        });
    });

    it('accepts custom limit parameter', function () {
        Http::fake([
            'api.spotify.com/v1/me/top/artists*' => Http::response([
                'items' => [],
                'total' => 0,
                'limit' => 50,
                'offset' => 0,
            ], 200),
        ]);

        $this->service->getUserTopArtists('medium_term', 50);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'limit=50');
        });
    });

    it('accepts both custom time range and limit', function () {
        Http::fake([
            'api.spotify.com/v1/me/top/artists*' => Http::response([
                'items' => [],
                'total' => 0,
                'limit' => 10,
                'offset' => 0,
            ], 200),
        ]);

        $this->service->getUserTopArtists('short_term', 10);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'time_range=short_term') &&
                   str_contains($request->url(), 'limit=10');
        });
    });

    it('throws exception when no access token is set', function () {
        $service = new SpotifyService;

        expect(fn () => $service->getUserTopArtists())
            ->toThrow(RuntimeException::class, 'Access token is required');
    });

    it('returns response with artists data', function () {
        $artistsData = [
            'items' => [
                [
                    'id' => 'artist1',
                    'name' => 'Test Artist 1',
                    'genres' => ['rock', 'pop'],
                    'popularity' => 85,
                ],
                [
                    'id' => 'artist2',
                    'name' => 'Test Artist 2',
                    'genres' => ['jazz', 'blues'],
                    'popularity' => 72,
                ],
            ],
            'total' => 2,
            'limit' => 20,
            'offset' => 0,
        ];

        Http::fake([
            'api.spotify.com/v1/me/top/artists*' => Http::response($artistsData, 200),
        ]);

        $response = $this->service->getUserTopArtists();

        expect($response->json())->toEqual($artistsData);
    });
});

describe('SpotifyService constructor and setAccessToken', function () {
    it('can be instantiated without access token', function () {
        $service = new SpotifyService;
        expect($service)->toBeInstanceOf(SpotifyService::class);
    });

    it('can be instantiated with access token', function () {
        $service = new SpotifyService('test-token');
        expect($service)->toBeInstanceOf(SpotifyService::class);
    });

    it('can set access token after instantiation', function () {
        $service = new SpotifyService;
        $result = $service->setAccessToken('new-token');
        expect($result)->toBe($service);
    });
});

describe('Albums', function () {
    it('getAlbum calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/albums/test-album-id' => Http::response(['id' => 'test-album-id'], 200),
        ]);

        $response = $this->service->getAlbum('test-album-id');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.spotify.com/v1/albums/test-album-id';
        });

        expect($response)->toBeInstanceOf(Response::class);
    });

    it('getAlbums calls correct endpoint with string ids', function () {
        Http::fake([
            'api.spotify.com/v1/albums*' => Http::response(['albums' => []], 200),
        ]);

        $this->service->getAlbums('id1,id2,id3');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'ids=id1%2Cid2%2Cid3');
        });
    });

    it('getAlbums calls correct endpoint with array ids', function () {
        Http::fake([
            'api.spotify.com/v1/albums*' => Http::response(['albums' => []], 200),
        ]);

        $this->service->getAlbums(['id1', 'id2', 'id3']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'ids=id1%2Cid2%2Cid3');
        });
    });

    it('getAlbumTracks calls correct endpoint with parameters', function () {
        Http::fake([
            'api.spotify.com/v1/albums/test-album/tracks*' => Http::response(['items' => []], 200),
        ]);

        $this->service->getAlbumTracks('test-album', 'US', 10, 5);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'albums/test-album/tracks') &&
                   str_contains($request->url(), 'market=US') &&
                   str_contains($request->url(), 'limit=10') &&
                   str_contains($request->url(), 'offset=5');
        });
    });

    it('getUserSavedAlbums calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/me/albums*' => Http::response(['items' => []], 200),
        ]);

        $this->service->getUserSavedAlbums(50, 10, 'US');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'me/albums') &&
                   str_contains($request->url(), 'limit=50') &&
                   str_contains($request->url(), 'offset=10') &&
                   str_contains($request->url(), 'market=US');
        });
    });

    it('checkUsersSavedAlbums calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/me/albums/contains*' => Http::response([true, false], 200),
        ]);

        $this->service->checkUsersSavedAlbums(['id1', 'id2']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'me/albums/contains') &&
                   str_contains($request->url(), 'ids=id1%2Cid2');
        });
    });

    it('getNewReleases calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/browse/new-releases*' => Http::response(['albums' => []], 200),
        ]);

        $this->service->getNewReleases(30, 15);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'browse/new-releases') &&
                   str_contains($request->url(), 'limit=30') &&
                   str_contains($request->url(), 'offset=15');
        });
    });
});

describe('Artists', function () {
    it('getArtist calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/artists/test-artist-id' => Http::response(['id' => 'test-artist-id'], 200),
        ]);

        $response = $this->service->getArtist('test-artist-id');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.spotify.com/v1/artists/test-artist-id';
        });

        expect($response)->toBeInstanceOf(Response::class);
    });

    it('getSeveralArtists calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/artists*' => Http::response(['artists' => []], 200),
        ]);

        $this->service->getSeveralArtists(['id1', 'id2']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/artists') &&
                   str_contains($request->url(), 'ids=id1%2Cid2');
        });
    });

    it('getArtistsAlbums calls correct endpoint with parameters', function () {
        Http::fake([
            'api.spotify.com/v1/artists/test-artist/albums*' => Http::response(['items' => []], 200),
        ]);

        $this->service->getArtistsAlbums('test-artist', ['album', 'single'], 'US', 25, 5);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'artists/test-artist/albums') &&
                   str_contains($request->url(), 'include_groups=album%2Csingle') &&
                   str_contains($request->url(), 'market=US') &&
                   str_contains($request->url(), 'limit=25') &&
                   str_contains($request->url(), 'offset=5');
        });
    });

    it('getArtistsTopTracks calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/artists/test-artist/top-tracks*' => Http::response(['tracks' => []], 200),
        ]);

        $this->service->getArtistsTopTracks('test-artist', 'US');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'artists/test-artist/top-tracks') &&
                   str_contains($request->url(), 'market=US');
        });
    });
});

describe('Audiobooks', function () {
    it('getAnAudiobook calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/audiobooks/test-audiobook-id' => Http::response(['id' => 'test-audiobook-id'], 200),
        ]);

        $response = $this->service->getAnAudiobook('test-audiobook-id');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.spotify.com/v1/audiobooks/test-audiobook-id';
        });

        expect($response)->toBeInstanceOf(Response::class);
    });

    it('getSeveralAudiobooks calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/audiobooks*' => Http::response(['audiobooks' => []], 200),
        ]);

        $this->service->getSeveralAudiobooks(['id1', 'id2'], 'US');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/audiobooks') &&
                   str_contains($request->url(), 'ids=id1%2Cid2') &&
                   str_contains($request->url(), 'market=US');
        });
    });

    it('getAudiobookChapters calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/audiobooks/test-audiobook/chapters*' => Http::response(['items' => []], 200),
        ]);

        $this->service->getAudiobookChapters('test-audiobook', 'US', 15, 3);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'audiobooks/test-audiobook/chapters') &&
                   str_contains($request->url(), 'market=US') &&
                   str_contains($request->url(), 'limit=15') &&
                   str_contains($request->url(), 'offset=3');
        });
    });

    it('getUsersSavedAudiobooks calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/me/audiobooks*' => Http::response(['items' => []], 200),
        ]);

        $this->service->getUsersSavedAudiobooks(25, 5);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'me/audiobooks') &&
                   str_contains($request->url(), 'limit=25') &&
                   str_contains($request->url(), 'offset=5');
        });
    });

    it('checkUsersSavedAudiobooks calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/me/audiobooks/contains*' => Http::response([true], 200),
        ]);

        $this->service->checkUsersSavedAudiobooks('test-id');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'me/audiobooks/contains') &&
                   str_contains($request->url(), 'ids=test-id');
        });
    });
});

describe('Categories', function () {
    it('getSeveralBrowseCategories calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/browse/categories*' => Http::response(['categories' => []], 200),
        ]);

        $this->service->getSeveralBrowseCategories('en_US', 30, 10);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'browse/categories') &&
                   str_contains($request->url(), 'locale=en_US') &&
                   str_contains($request->url(), 'limit=30') &&
                   str_contains($request->url(), 'offset=10');
        });
    });

    it('getSingleBrowseCategory calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/browse/categories/test-category*' => Http::response(['id' => 'test-category'], 200),
        ]);

        $this->service->getSingleBrowseCategory('test-category', 'en_US');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'browse/categories/test-category') &&
                   str_contains($request->url(), 'locale=en_US');
        });
    });
});

describe('Chapters', function () {
    it('getAChapter calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/chapters/test-chapter-id*' => Http::response(['id' => 'test-chapter-id'], 200),
        ]);

        $this->service->getAChapter('test-chapter-id', 'US');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'chapters/test-chapter-id') &&
                   str_contains($request->url(), 'market=US');
        });
    });

    it('getSeveralChapters calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/chapters*' => Http::response(['chapters' => []], 200),
        ]);

        $this->service->getSeveralChapters(['id1', 'id2'], 'US');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/chapters') &&
                   str_contains($request->url(), 'ids=id1%2Cid2') &&
                   str_contains($request->url(), 'market=US');
        });
    });
});

describe('Episodes', function () {
    it('getEpisode calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/episodes/test-episode-id*' => Http::response(['id' => 'test-episode-id'], 200),
        ]);

        $this->service->getEpisode('test-episode-id', 'US');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'episodes/test-episode-id') &&
                   str_contains($request->url(), 'market=US');
        });
    });

    it('getSeveralEpisodes calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/episodes*' => Http::response(['episodes' => []], 200),
        ]);

        $this->service->getSeveralEpisodes(['id1', 'id2'], 'US');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/episodes') &&
                   str_contains($request->url(), 'ids=id1%2Cid2') &&
                   str_contains($request->url(), 'market=US');
        });
    });

    it('getUsersSavedEpisodes calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/me/episodes*' => Http::response(['items' => []], 200),
        ]);

        $this->service->getUsersSavedEpisodes('US', 15, 5);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'me/episodes') &&
                   str_contains($request->url(), 'market=US') &&
                   str_contains($request->url(), 'limit=15') &&
                   str_contains($request->url(), 'offset=5');
        });
    });

    it('checkUsersSavedEpisodes calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/me/episodes/contains*' => Http::response([true], 200),
        ]);

        $this->service->checkUsersSavedEpisodes('test-id');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'me/episodes/contains') &&
                   str_contains($request->url(), 'ids=test-id');
        });
    });
});

describe('Markets', function () {
    it('getAvailableMarkets calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/markets' => Http::response(['markets' => []], 200),
        ]);

        $response = $this->service->getAvailableMarkets();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.spotify.com/v1/markets';
        });

        expect($response)->toBeInstanceOf(Response::class);
    });
});

describe('Player', function () {
    it('getPlaybackState calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/me/player*' => Http::response(['device' => []], 200),
        ]);

        $this->service->getPlaybackState('US', ['track', 'episode']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'me/player') &&
                   str_contains($request->url(), 'market=US') &&
                   str_contains($request->url(), 'additional_types=track%2Cepisode');
        });
    });

    it('getAvailableDevices calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/me/player/devices' => Http::response(['devices' => []], 200),
        ]);

        $response = $this->service->getAvailableDevices();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.spotify.com/v1/me/player/devices';
        });

        expect($response)->toBeInstanceOf(Response::class);
    });

    it('getCurrentlyPlayingTrack calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/me/player/currently-playing*' => Http::response(['item' => []], 200),
        ]);

        $this->service->getCurrentlyPlayingTrack('US', 'track');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'me/player/currently-playing') &&
                   str_contains($request->url(), 'market=US') &&
                   str_contains($request->url(), 'additional_types=track');
        });
    });

    it('getRecentlyPlayedTracks calls correct endpoint with after parameter', function () {
        Http::fake([
            'api.spotify.com/v1/me/player/recently-played*' => Http::response(['items' => []], 200),
        ]);

        $this->service->getRecentlyPlayedTracks(15, 1234567890);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'me/player/recently-played') &&
                   str_contains($request->url(), 'limit=15') &&
                   str_contains($request->url(), 'after=1234567890');
        });
    });

    it('getRecentlyPlayedTracks calls correct endpoint with before parameter', function () {
        Http::fake([
            'api.spotify.com/v1/me/player/recently-played*' => Http::response(['items' => []], 200),
        ]);

        $this->service->getRecentlyPlayedTracks(15, null, 1234567890);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'me/player/recently-played') &&
                   str_contains($request->url(), 'limit=15') &&
                   str_contains($request->url(), 'before=1234567890');
        });
    });

    it('getRecentlyPlayedTracks throws exception when both after and before are provided', function () {
        expect(fn () => $this->service->getRecentlyPlayedTracks(20, 123, 456))
            ->toThrow(RuntimeException::class, 'Only one of after or before can be provided');
    });

    it('getTheUsersQueue calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/me/player/queue' => Http::response(['queue' => []], 200),
        ]);

        $response = $this->service->getTheUsersQueue();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.spotify.com/v1/me/player/queue';
        });

        expect($response)->toBeInstanceOf(Response::class);
    });
});

describe('Playlists', function () {
    it('getPlaylist calls correct endpoint with all parameters', function () {
        Http::fake([
            'api.spotify.com/v1/playlists/test-playlist*' => Http::response(['id' => 'test-playlist'], 200),
        ]);

        $this->service->getPlaylist('test-playlist', 'US', ['name', 'tracks'], ['track']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'playlists/test-playlist') &&
                   str_contains($request->url(), 'market=US') &&
                   str_contains($request->url(), 'fields=name%2Ctracks') &&
                   str_contains($request->url(), 'additional_types=track');
        });
    });

    it('getPlaylistItems calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/playlists/test-playlist/tracks*' => Http::response(['items' => []], 200),
        ]);

        $this->service->getPlaylistItems('test-playlist', 'US', 'name,track', 25, 5, 'track');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'playlists/test-playlist/tracks') &&
                   str_contains($request->url(), 'market=US') &&
                   str_contains($request->url(), 'fields=name%2Ctrack') &&
                   str_contains($request->url(), 'limit=25') &&
                   str_contains($request->url(), 'offset=5') &&
                   str_contains($request->url(), 'additional_types=track');
        });
    });

    it('getCurrentUsersPlaylists calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/me/playlists*' => Http::response(['items' => []], 200),
        ]);

        $this->service->getCurrentUsersPlaylists(30, 10);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'me/playlists') &&
                   str_contains($request->url(), 'limit=30') &&
                   str_contains($request->url(), 'offset=10');
        });
    });

    it('getUsersPlaylists calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/users/test-user/playlists*' => Http::response(['items' => []], 200),
        ]);

        $this->service->getUsersPlaylists('test-user', 25, 5);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'users/test-user/playlists') &&
                   str_contains($request->url(), 'limit=25') &&
                   str_contains($request->url(), 'offset=5');
        });
    });

    it('getPlaylistCoverImage calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/playlists/test-playlist/images' => Http::response([['url' => 'image.jpg']], 200),
        ]);

        $response = $this->service->getPlaylistCoverImage('test-playlist');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.spotify.com/v1/playlists/test-playlist/images';
        });

        expect($response)->toBeInstanceOf(Response::class);
    });
});

describe('Search', function () {
    it('searchForItem calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/search*' => Http::response(['tracks' => ['items' => []]], 200),
        ]);

        $this->service->searchForItem('test query', ['track', 'artist'], 'US', 25, 5, 'audio');

        Http::assertSent(function ($request) {
            $url = $request->url();

            return str_contains($url, '/search') &&
                   (str_contains($url, 'q=test+query') || str_contains($url, 'q=test%20query')) &&
                   str_contains($url, 'type=track%2Cartist') &&
                   str_contains($url, 'market=US') &&
                   str_contains($url, 'limit=25') &&
                   str_contains($url, 'offset=5') &&
                   str_contains($url, 'include_external=audio');
        });
    });
});

describe('Shows', function () {
    it('getShow calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/shows/test-show-id*' => Http::response(['id' => 'test-show-id'], 200),
        ]);

        $this->service->getShow('test-show-id', 'US');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'shows/test-show-id') &&
                   str_contains($request->url(), 'market=US');
        });
    });

    it('getSeveralShows calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/shows*' => Http::response(['shows' => []], 200),
        ]);

        $this->service->getSeveralShows(['id1', 'id2'], 'US');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/shows') &&
                   str_contains($request->url(), 'ids=id1%2Cid2') &&
                   str_contains($request->url(), 'market=US');
        });
    });

    it('getShowEpisodes calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/shows/test-show/episodes*' => Http::response(['items' => []], 200),
        ]);

        $this->service->getShowEpisodes('test-show', 'US', 15, 3);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'shows/test-show/episodes') &&
                   str_contains($request->url(), 'market=US') &&
                   str_contains($request->url(), 'limit=15') &&
                   str_contains($request->url(), 'offset=3');
        });
    });

    it('getUsersSavedShows calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/me/shows*' => Http::response(['items' => []], 200),
        ]);

        $this->service->getUsersSavedShows(25, 5);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'me/shows') &&
                   str_contains($request->url(), 'limit=25') &&
                   str_contains($request->url(), 'offset=5');
        });
    });

    it('checkUsersSavedShows calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/me/shows/contains*' => Http::response([true], 200),
        ]);

        $this->service->checkUsersSavedShows('test-id');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'me/shows/contains') &&
                   str_contains($request->url(), 'ids=test-id');
        });
    });
});

describe('Tracks', function () {
    it('getTrack calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/tracks/test-track-id*' => Http::response(['id' => 'test-track-id'], 200),
        ]);

        $this->service->getTrack('test-track-id', 'US');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'tracks/test-track-id') &&
                   str_contains($request->url(), 'market=US');
        });
    });

    it('getSeveralTracks calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/tracks*' => Http::response(['tracks' => []], 200),
        ]);

        $this->service->getSeveralTracks(['id1', 'id2'], 'US');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/tracks') &&
                   str_contains($request->url(), 'ids=id1%2Cid2') &&
                   str_contains($request->url(), 'market=US');
        });
    });

    it('getUsersSavedTracks calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/me/tracks*' => Http::response(['items' => []], 200),
        ]);

        $this->service->getUsersSavedTracks('US', 25, 5);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'me/tracks') &&
                   str_contains($request->url(), 'market=US') &&
                   str_contains($request->url(), 'limit=25') &&
                   str_contains($request->url(), 'offset=5');
        });
    });

    it('checkUsersSavedTracks calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/me/tracks/contains*' => Http::response([true], 200),
        ]);

        $this->service->checkUsersSavedTracks('test-id');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'me/tracks/contains') &&
                   str_contains($request->url(), 'ids=test-id');
        });
    });
});

describe('Users', function () {
    it('getCurrentUsersProfile calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/me' => Http::response(['id' => 'current-user'], 200),
        ]);

        $response = $this->service->getCurrentUsersProfile();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.spotify.com/v1/me';
        });

        expect($response)->toBeInstanceOf(Response::class);
    });

    it('getUsersProfile calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/users/test-user' => Http::response(['id' => 'test-user'], 200),
        ]);

        $response = $this->service->getUsersProfile('test-user');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.spotify.com/v1/users/test-user';
        });

        expect($response)->toBeInstanceOf(Response::class);
    });

    it('getFollowedArtists calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/me/following*' => Http::response(['artists' => ['items' => []]], 200),
        ]);

        $this->service->getFollowedArtists('after-id', 30);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'me/following') &&
                   str_contains($request->url(), 'type=artist') &&
                   str_contains($request->url(), 'after=after-id') &&
                   str_contains($request->url(), 'limit=30');
        });
    });

    it('checkIfUserFollowsArtistsOrUsers calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/me/following/contains*' => Http::response([true], 200),
        ]);

        $this->service->checkIfUserFollowsArtistsOrUsers(['id1', 'id2'], 'user');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'me/following/contains') &&
                   str_contains($request->url(), 'type=user') &&
                   str_contains($request->url(), 'ids=id1%2Cid2');
        });
    });

    it('checkIfCurrentUserFollowsPlaylist calls correct endpoint', function () {
        Http::fake([
            'api.spotify.com/v1/playlists/test-playlist/followers/contains*' => Http::response([true], 200),
        ]);

        $this->service->checkIfCurrentUserFollowsPlaylist('test-playlist', ['user1', 'user2']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'playlists/test-playlist/followers/contains') &&
                   str_contains($request->url(), 'ids=user1%2Cuser2');
        });
    });

    it('getAuthenticatedUser calls getCurrentUsersProfile', function () {
        Http::fake([
            'api.spotify.com/v1/me' => Http::response(['id' => 'current-user'], 200),
        ]);

        $response = $this->service->getAuthenticatedUser();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.spotify.com/v1/me';
        });

        expect($response)->toBeInstanceOf(Response::class);
    });
});
