<?php

namespace emmpaul\LaravelSpotify\Services;

use emmpaul\LaravelSpotify\Enums\SpotifyRepeatState;
use emmpaul\LaravelSpotify\Enums\SpotifyTimeRange;
use emmpaul\LaravelSpotify\Enums\SpotifyTopType;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Service for interacting with the Spotify Web API.
 *
 * Provides methods to fetch user information, playlists, tracks, artists, albums,
 * and top statistics using an access token.
 */
class SpotifyService
{
    /**
     * Base URL for the Spotify Web API.
     */
    protected string $baseUrl;

    /**
     * The access token used for Spotify API authentication.
     */
    protected ?string $accessToken = null;

    /**
     * Create a new SpotifyService instance.
     *
     * @param  string|null  $accessToken  Spotify access token (optional).
     */
    public function __construct(?string $accessToken = null)
    {
        $this->baseUrl = config('spotify.api_base_url');
        $this->accessToken = $accessToken;
    }

    /**
     * Set the Spotify access token.
     *
     * @param  string  $accessToken  Spotify access token.
     * @return $this
     */
    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Normalize string or array to comma-separated string.
     *
     * @param  string|array<string>  $value  Value to normalize.
     * @return string Comma-separated string.
     */
    protected function normalizeToCommaSeparated(string|array $value): string
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    protected function clampLimit(int $limit): int
    {
        return max(1, min(50, $limit));
    }

    /**
     * @param  string|array<string>  $value  Value to normalize.
     * @return array<string>
     */
    protected function normalizeToArray(string|array $value): array
    {
        return is_array($value) ? $value : explode(',', $value);
    }

    /**
     * Make an HTTP request to the Spotify API.
     *
     * @param  string  $method  HTTP method (e.g., 'get', 'post').
     * @param  string  $endpoint  Spotify API endpoint (relative to base URL).
     * @param  array<string, mixed>  $data  Query or body parameters.
     * @return Response The HTTP response from Spotify API.
     *
     * @throws RuntimeException If no access token is set.
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): Response
    {
        if (! $this->accessToken) {
            throw new RuntimeException('Access token is required');
        }

        $headers = [
            'Authorization' => 'Bearer '.$this->accessToken,
            'Content-Type' => 'application/json',
        ];

        if ($method === 'delete') {
            return Http::withHeaders($headers)->send('DELETE', $this->baseUrl.$endpoint, ['json' => $data]);
        }

        return Http::withHeaders($headers)->$method($this->baseUrl.$endpoint, $data);
    }

    // Albums

    /**
     * Get a specific album by ID.
     *
     * @param  string  $albumId  Album ID.
     *
     * @throws Exception
     */
    public function getAlbum(string $albumId): Response
    {
        return $this->makeRequest('get', '/albums/'.$albumId);
    }

    /**
     * Get several albums by their IDs.
     *
     * @param  string|array<string>  $albumIds  Album IDs (comma-separated string or array).
     * @param  string|null  $market  Market to retrieve content for (default: null).
     * @return Response The HTTP response containing album data.
     */
    public function getAlbums(string|array $albumIds, ?string $market = null): Response
    {
        return $this->makeRequest('get', '/albums', [
            'ids' => $this->normalizeToCommaSeparated($albumIds),
            'market' => $market,
        ]);
    }

    /**
     * Get tracks from a specific album.
     *
     * @param  string  $albumId  Album ID.
     * @param  string|null  $market  Market to retrieve content for (default: null).
     * @param  int  $limit  Maximum number of items to return (default: 20).
     * @param  int  $offset  Index of the first item to return (default: 0).
     * @return Response The HTTP response containing album tracks.
     */
    public function getAlbumTracks(string $albumId, ?string $market = null, int $limit = 20, int $offset = 0): Response
    {
        $limit = $this->clampLimit($limit);

        return $this->makeRequest('get', '/albums/'.$albumId.'/tracks', [
            'market' => $market,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get the current user's saved albums.
     *
     * @param  int  $limit  Maximum number of items to return (default: 20).
     * @param  int  $offset  Index of the first item to return (default: 0).
     * @param  string|null  $market  Market to retrieve content for (default: null).
     * @return Response The HTTP response containing saved albums.
     */
    public function getUserSavedAlbums(int $limit = 20, int $offset = 0, ?string $market = null): Response
    {
        $limit = $this->clampLimit($limit);

        return $this->makeRequest('get', '/me/albums', [
            'limit' => $limit,
            'offset' => $offset,
            'market' => $market,
        ]);
    }

    /**
     * Check if one or more albums are already saved in the current Spotify user's 'Your Music' library.
     *
     * @param  string|array<string>  $albumIds  Album IDs to check (comma-separated string or array).
     * @return Response The HTTP response containing boolean array indicating if albums are saved.
     */
    public function checkUsersSavedAlbums(string|array $albumIds): Response
    {
        return $this->makeRequest('get', '/me/albums/contains', [
            'ids' => $this->normalizeToCommaSeparated($albumIds),
        ]);
    }

    /**
     * Save one or more albums to the current user's 'Your Music' library.
     *
     * @param  string|array<string>  $albumIds  Album IDs to save (comma-separated string or array).
     * @return Response The HTTP response.
     */
    public function saveAlbumsForCurrentUser(string|array $albumIds): Response
    {
        return $this->makeRequest('put', '/me/albums', [
            'ids' => $this->normalizeToArray($albumIds),
        ]);
    }

    /**
     * Remove one or more albums from the current user's 'Your Music' library.
     *
     * @param  string|array<string>  $albumIds  Album IDs to remove (comma-separated string or array).
     * @return Response The HTTP response.
     */
    public function removeUsersSavedAlbums(string|array $albumIds): Response
    {
        return $this->makeRequest('delete', '/me/albums', [
            'ids' => $this->normalizeToArray($albumIds),
        ]);
    }

    /**
     * Get new album releases.
     *
     * @param  int  $limit  Maximum number of items to return (default: 20).
     * @param  int  $offset  Index of the first item to return (default: 0).
     * @return Response The HTTP response containing new releases.
     */
    public function getNewReleases(int $limit = 20, int $offset = 0): Response
    {
        $limit = $this->clampLimit($limit);

        return $this->makeRequest('get', '/browse/new-releases', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    // Artists

    /**
     * Get a specific artist by ID.
     *
     * @param  string  $artistId  Artist ID.
     *
     * @throws Exception
     */
    public function getArtist(string $artistId): Response
    {
        return $this->makeRequest('get', '/artists/'.$artistId);
    }

    /**
     * Get several artists by their IDs.
     *
     * @param  string|array<string>  $artistIds  Artist IDs (comma-separated string or array).
     * @return Response The HTTP response containing artist data.
     */
    public function getSeveralArtists(string|array $artistIds): Response
    {
        return $this->makeRequest('get', '/artists', [
            'ids' => $this->normalizeToCommaSeparated($artistIds),
        ]);
    }

    /**
     * Get albums for a specific artist.
     *
     * @param  string  $artistId  Artist ID.
     * @param  string|array<string>  $include_groups  Include groups (album, single, appears_on, compilation).
     * @param  string|null  $market  Market to retrieve content for (default: null).
     * @param  int  $limit  Maximum number of items to return (default: 20).
     * @param  int  $offset  Index of the first item to return (default: 0).
     * @return Response The HTTP response containing artist albums.
     */
    public function getArtistsAlbums(string $artistId, string|array $include_groups = [], ?string $market = null, int $limit = 20, int $offset = 0): Response
    {
        $limit = $this->clampLimit($limit);

        return $this->makeRequest('get', '/artists/'.$artistId.'/albums', [
            'include_groups' => $this->normalizeToCommaSeparated($include_groups),
            'market' => $market,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get an artist's top tracks.
     *
     * @param  string  $artistId  Artist ID.
     * @param  string|null  $market  Market to retrieve content for (default: null).
     * @return Response The HTTP response containing top tracks.
     */
    public function getArtistsTopTracks(string $artistId, ?string $market = null): Response
    {
        return $this->makeRequest('get', '/artists/'.$artistId.'/top-tracks', [
            'market' => $market,
        ]);
    }

    // Audiobooks

    /**
     * Get a specific audiobook by ID.
     *
     * @param  string  $audiobookId  Audiobook ID.
     * @return Response The HTTP response containing audiobook data.
     */
    public function getAnAudiobook(string $audiobookId): Response
    {
        return $this->makeRequest('get', '/audiobooks/'.$audiobookId);
    }

    /**
     * Get several audiobooks by their IDs.
     *
     * @param  string|array<string>  $audiobookIds  Audiobook IDs (comma-separated string or array).
     * @param  string|null  $market  Market to retrieve content for (default: null).
     * @return Response The HTTP response containing audiobook data.
     */
    public function getSeveralAudiobooks(string|array $audiobookIds, ?string $market = null): Response
    {
        return $this->makeRequest('get', '/audiobooks', [
            'ids' => $this->normalizeToCommaSeparated($audiobookIds),
            'market' => $market,
        ]);
    }

    /**
     * Get chapters from a specific audiobook.
     *
     * @param  string  $audiobookId  Audiobook ID.
     * @param  string|null  $market  Market to retrieve content for (default: null).
     * @param  int  $limit  Maximum number of items to return (default: 20).
     * @param  int  $offset  Index of the first item to return (default: 0).
     * @return Response The HTTP response containing audiobook chapters.
     */
    public function getAudiobookChapters(string $audiobookId, ?string $market = null, int $limit = 20, int $offset = 0): Response
    {
        $limit = $this->clampLimit($limit);

        return $this->makeRequest('get', '/audiobooks/'.$audiobookId.'/chapters', [
            'market' => $market,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get the current user's saved audiobooks.
     *
     * @param  int  $limit  Maximum number of items to return (default: 20).
     * @param  int  $offset  Index of the first item to return (default: 0).
     * @return Response The HTTP response containing saved audiobooks.
     */
    public function getUsersSavedAudiobooks(int $limit = 20, int $offset = 0): Response
    {
        $limit = $this->clampLimit($limit);

        return $this->makeRequest('get', '/me/audiobooks', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Check if one or more audiobooks are saved in the user's library.
     *
     * @param  string|array<string>  $audiobookIds  Audiobook IDs to check (comma-separated string or array).
     * @return Response The HTTP response containing boolean array indicating if audiobooks are saved.
     */
    public function checkUsersSavedAudiobooks(string|array $audiobookIds): Response
    {
        return $this->makeRequest('get', '/me/audiobooks/contains', [
            'ids' => $this->normalizeToCommaSeparated($audiobookIds),
        ]);
    }

    // Categories

    /**
     * Get several browse categories.
     *
     * @param  string|null  $locale  Locale for category names (default: null).
     * @param  int  $limit  Maximum number of items to return (default: 20).
     * @param  int  $offset  Index of the first item to return (default: 0).
     * @return Response The HTTP response containing browse categories.
     */
    public function getSeveralBrowseCategories(?string $locale = null, int $limit = 20, int $offset = 0): Response
    {
        $limit = $this->clampLimit($limit);

        return $this->makeRequest('get', '/browse/categories', [
            'locale' => $locale,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get a single browse category.
     *
     * @param  string  $categoryId  Category ID.
     * @param  string|null  $locale  Locale for category names (default: null).
     * @return Response The HTTP response containing browse category data.
     */
    public function getSingleBrowseCategory(string $categoryId, ?string $locale = null): Response
    {
        return $this->makeRequest('get', '/browse/categories/'.$categoryId, [
            'locale' => $locale,
        ]);
    }

    // Chapters

    /**
     * Get a specific chapter by ID.
     *
     * @param  string  $chapterId  Chapter ID.
     * @param  string|null  $market  Market to retrieve content for (default: null).
     * @return Response The HTTP response containing chapter data.
     */
    public function getAChapter(string $chapterId, ?string $market = null): Response
    {
        return $this->makeRequest('get', '/chapters/'.$chapterId, [
            'market' => $market,
        ]);
    }

    /**
     * Get several chapters by their IDs.
     *
     * @param  string|array<string>  $chapterIds  Chapter IDs (comma-separated string or array).
     * @param  string|null  $market  Market to retrieve content for (default: null).
     * @return Response The HTTP response containing chapter data.
     */
    public function getSeveralChapters(string|array $chapterIds, ?string $market = null): Response
    {
        return $this->makeRequest('get', '/chapters', [
            'ids' => $this->normalizeToCommaSeparated($chapterIds),
            'market' => $market,
        ]);
    }

    /**
     * Get a specific episode by ID.
     *
     * @param  string  $episodeId  Episode ID.
     * @param  string|null  $market  Market to retrieve content for (default: null).
     * @return Response The HTTP response containing episode data.
     */
    public function getEpisode(string $episodeId, ?string $market = null): Response
    {
        return $this->makeRequest('get', '/episodes/'.$episodeId, [
            'market' => $market,
        ]);
    }

    /**
     * Get several episodes by their IDs.
     *
     * @param  string|array<string>  $episodeIds  Episode IDs (comma-separated string or array).
     * @param  string|null  $market  Market to retrieve content for (default: null).
     * @return Response The HTTP response containing episode data.
     */
    public function getSeveralEpisodes(string|array $episodeIds, ?string $market = null): Response
    {
        return $this->makeRequest('get', '/episodes', [
            'ids' => $this->normalizeToCommaSeparated($episodeIds),
            'market' => $market,
        ]);
    }

    /**
     * Get the current user's saved episodes.
     *
     * @param  string|null  $market  Market to retrieve content for (default: null).
     * @param  int  $limit  Maximum number of items to return (default: 20).
     * @param  int  $offset  Index of the first item to return (default: 0).
     * @return Response The HTTP response containing saved episodes.
     */
    public function getUsersSavedEpisodes(?string $market = null, int $limit = 20, int $offset = 0): Response
    {
        $limit = $this->clampLimit($limit);

        return $this->makeRequest('get', '/me/episodes', [
            'market' => $market,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Check if one or more episodes are saved in the user's library.
     *
     * @param  string|array<string>  $episodeIds  Episode IDs to check (comma-separated string or array).
     * @return Response The HTTP response containing boolean array indicating if episodes are saved.
     */
    public function checkUsersSavedEpisodes(string|array $episodeIds): Response
    {
        return $this->makeRequest('get', '/me/episodes/contains', [
            'ids' => $this->normalizeToCommaSeparated($episodeIds),
        ]);
    }

    /**
     * Save one or more episodes to the current user's library.
     *
     * @param  string|array<string>  $episodeIds  Episode IDs to save (comma-separated string or array).
     * @return Response The HTTP response.
     */
    public function saveEpisodesForCurrentUser(string|array $episodeIds): Response
    {
        return $this->makeRequest('put', '/me/episodes', [
            'ids' => $this->normalizeToArray($episodeIds),
        ]);
    }

    /**
     * Remove one or more episodes from the current user's library.
     *
     * @param  string|array<string>  $episodeIds  Episode IDs to remove (comma-separated string or array).
     * @return Response The HTTP response.
     */
    public function removeUsersSavedEpisodes(string|array $episodeIds): Response
    {
        return $this->makeRequest('delete', '/me/episodes', [
            'ids' => $this->normalizeToArray($episodeIds),
        ]);
    }

    // Markets

    /**
     * Get available markets.
     *
     * @return Response The HTTP response containing available markets.
     */
    public function getAvailableMarkets(): Response
    {
        return $this->makeRequest('get', '/markets');
    }

    // Player

    /**
     * Get the current user's playback state.
     *
     * @param  string|null  $market  Market to retrieve content for (default: null).
     * @param  string|array<string>|null  $additional_types  Additional types to include (default: null).
     * @return Response The HTTP response containing playback state.
     */
    public function getPlaybackState(?string $market = null, string|array|null $additional_types = null): Response
    {
        return $this->makeRequest('get', '/me/player', [
            'market' => $market,
            'additional_types' => $additional_types ? $this->normalizeToCommaSeparated($additional_types) : null,
        ]);
    }

    /**
     * Get the user's available devices.
     *
     * @return Response The HTTP response containing available devices.
     */
    public function getAvailableDevices(): Response
    {
        return $this->makeRequest('get', '/me/player/devices');
    }

    /**
     * Get the user's currently playing track.
     *
     * @param  string|null  $market  Market to retrieve content for (default: null).
     * @param  string|array<string>|null  $additional_types  Additional types to include (default: null).
     * @return Response The HTTP response containing currently playing track.
     */
    public function getCurrentlyPlayingTrack(?string $market = null, string|array|null $additional_types = null): Response
    {
        return $this->makeRequest('get', '/me/player/currently-playing', [
            'market' => $market,
            'additional_types' => $additional_types ? $this->normalizeToCommaSeparated($additional_types) : null,
        ]);
    }

    /**
     * Get the user's recently played tracks.
     *
     * @param  int  $limit  Maximum number of items to return (default: 20).
     * @param  int|null  $after  Unix timestamp to get tracks after (default: null).
     * @param  int|null  $before  Unix timestamp to get tracks before (default: null).
     * @return Response The HTTP response containing recently played tracks.
     *
     * @throws RuntimeException If both after and before are provided.
     */
    public function getRecentlyPlayedTracks(int $limit = 20, ?int $after = null, ?int $before = null): Response
    {
        $limit = $this->clampLimit($limit);

        if ($after && $before) {
            throw new RuntimeException('Only one of after or before can be provided');
        }

        return $this->makeRequest('get', '/me/player/recently-played', [
            'limit' => $limit,
            'after' => $after,
            'before' => $before,
        ]);
    }

    /**
     * Get the user's queue.
     *
     * @return Response The HTTP response containing user's queue.
     */
    public function getTheUsersQueue(): Response
    {
        return $this->makeRequest('get', '/me/player/queue');
    }

    /**
     * @param  string|array<string>|null  $trackIds
     */
    public function resumePlayback(string $deviceId, string|array|null $trackIds = null): Response
    {
        return $this->makeRequest('put', '/me/player/play', [
            'device_id' => $deviceId,
            'uris' => $trackIds ? $this->formatForTracksPlayback($trackIds) : null,
        ]);
    }

    public function pausePlayback(string $deviceId): Response
    {
        return $this->makeRequest('put', '/me/player/pause', [
            'device_id' => $deviceId,
        ]);
    }

    /**
     * Skip to the next track in the user's queue.
     *
     * @param  string|null  $deviceId  The ID of the device to target (default: null).
     * @return Response The HTTP response.
     */
    public function skipToNext(?string $deviceId = null): Response
    {
        return $this->makeRequest('post', '/me/player/next', array_filter([
            'device_id' => $deviceId,
        ]));
    }

    /**
     * Skip to the previous track in the user's queue.
     *
     * @param  string|null  $deviceId  The ID of the device to target (default: null).
     * @return Response The HTTP response.
     */
    public function skipToPrevious(?string $deviceId = null): Response
    {
        return $this->makeRequest('post', '/me/player/previous', array_filter([
            'device_id' => $deviceId,
        ]));
    }

    /**
     * Seek to a position in the currently playing track.
     *
     * @param  int  $positionMs  The position in milliseconds to seek to.
     * @param  string|null  $deviceId  The ID of the device to target (default: null).
     * @return Response The HTTP response.
     */
    public function seekToPosition(int $positionMs, ?string $deviceId = null): Response
    {
        $params = ['position_ms' => $positionMs];

        if ($deviceId !== null) {
            $params['device_id'] = $deviceId;
        }

        return $this->makeRequest('put', '/me/player/seek', $params);
    }

    /**
     * Set the repeat mode for the user's playback.
     *
     * @param  SpotifyRepeatState|string  $state  The repeat state (track, context, off).
     * @param  string|null  $deviceId  The ID of the device to target (default: null).
     * @return Response The HTTP response.
     */
    public function setRepeatMode(SpotifyRepeatState|string $state, ?string $deviceId = null): Response
    {
        $stateValue = $state instanceof SpotifyRepeatState ? $state->value : $state;

        return $this->makeRequest('put', '/me/player/repeat', array_filter([
            'state' => $stateValue,
            'device_id' => $deviceId,
        ]));
    }

    /**
     * Toggle shuffle on or off for the user's playback.
     *
     * @param  bool  $state  Whether to enable shuffle.
     * @param  string|null  $deviceId  The ID of the device to target (default: null).
     * @return Response The HTTP response.
     */
    public function toggleShuffle(bool $state, ?string $deviceId = null): Response
    {
        $params = ['state' => $state];

        if ($deviceId !== null) {
            $params['device_id'] = $deviceId;
        }

        return $this->makeRequest('put', '/me/player/shuffle', $params);
    }

    /**
     * Set the volume for the user's playback.
     *
     * @param  int  $volumePercent  The volume to set (0-100).
     * @param  string|null  $deviceId  The ID of the device to target (default: null).
     * @return Response The HTTP response.
     */
    public function setPlaybackVolume(int $volumePercent, ?string $deviceId = null): Response
    {
        $volumePercent = max(0, min(100, $volumePercent));

        $params = ['volume_percent' => $volumePercent];

        if ($deviceId !== null) {
            $params['device_id'] = $deviceId;
        }

        return $this->makeRequest('put', '/me/player/volume', $params);
    }

    /**
     * Transfer playback to a new device.
     *
     * @param  string|array<string>  $deviceIds  The ID(s) of the device(s) to transfer to.
     * @param  bool  $play  Whether to start playing on the new device (default: false).
     * @return Response The HTTP response.
     */
    public function transferPlayback(string|array $deviceIds, bool $play = false): Response
    {
        return $this->makeRequest('put', '/me/player', [
            'device_ids' => is_array($deviceIds) ? $deviceIds : [$deviceIds],
            'play' => $play,
        ]);
    }

    /**
     * Add an item to the end of the user's current playback queue.
     *
     * @param  string  $uri  The URI of the item to add (e.g. spotify:track:xxx).
     * @param  string|null  $deviceId  The ID of the device to target (default: null).
     * @return Response The HTTP response.
     */
    public function addItemToPlaybackQueue(string $uri, ?string $deviceId = null): Response
    {
        return $this->makeRequest('post', '/me/player/queue', array_filter([
            'uri' => $uri,
            'device_id' => $deviceId,
        ]));
    }

    // Playlists

    /**
     * Get a specific playlist by ID.
     *
     * @param  string  $playlistId  Playlist ID.
     * @param  array<string>  $fields  Optional fields to return (comma-separated or array).
     * @param  array<string>  $additional_types  Additional types to return (comma-separated or array).
     *
     * @throws Exception
     */
    public function getPlaylist(string $playlistId, ?string $market = null, string|array|null $fields = null, string|array|null $additional_types = null): Response
    {
        return $this->makeRequest('get', '/playlists/'.$playlistId, [
            'market' => $market,
            'fields' => is_array($fields) ? implode(',', $fields) : $fields,
            'additional_types' => is_array($additional_types) ? implode(',', $additional_types) : $additional_types,
        ]);
    }

    /**
     * Get items from a playlist.
     *
     * @param  string  $playlistId  Playlist ID.
     * @param  string|null  $market  Market to retrieve content for (default: null).
     * @param  string|array<string>|null  $fields  Fields to return (default: null).
     * @param  int  $limit  Maximum number of items to return (default: 20).
     * @param  int  $offset  Index of the first item to return (default: 0).
     * @param  string|array<string>|null  $additional_types  Additional types to include (default: null).
     * @return Response The HTTP response containing playlist items.
     */
    public function getPlaylistItems(string $playlistId, ?string $market = null, string|array|null $fields = null, int $limit = 20, int $offset = 0, string|array|null $additional_types = null): Response
    {
        $limit = $this->clampLimit($limit);

        return $this->makeRequest('get', '/playlists/'.$playlistId.'/tracks', [
            'market' => $market,
            'fields' => $fields ? $this->normalizeToCommaSeparated($fields) : null,
            'limit' => $limit,
            'offset' => $offset,
            'additional_types' => $additional_types ? $this->normalizeToCommaSeparated($additional_types) : null,
        ]);
    }

    /**
     * Get the current user's playlists.
     *
     * @param  int  $limit  Maximum number of items to return (default: 20).
     * @param  int  $offset  Index of the first item to return (default: 0).
     * @return Response The HTTP response containing user's playlists.
     */
    public function getCurrentUsersPlaylists(int $limit = 20, int $offset = 0): Response
    {
        $limit = $this->clampLimit($limit);

        return $this->makeRequest('get', '/me/playlists', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get a user's playlists.
     *
     * @param  string  $userId  User ID.
     * @param  int  $limit  Maximum number of items to return (default: 20).
     * @param  int  $offset  Index of the first item to return (default: 0).
     * @return Response The HTTP response containing user's playlists.
     */
    public function getUsersPlaylists(string $userId, int $limit = 20, int $offset = 0): Response
    {
        $limit = $this->clampLimit($limit);

        return $this->makeRequest('get', '/users/'.$userId.'/playlists', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get playlist cover image.
     *
     * @param  string  $playlistId  Playlist ID.
     * @return Response The HTTP response containing playlist cover images.
     */
    public function getPlaylistCoverImage(string $playlistId): Response
    {
        return $this->makeRequest('get', '/playlists/'.$playlistId.'/images');
    }

    /**
     * Create a playlist for a Spotify user.
     *
     * @param  string  $userId  User ID.
     * @param  string  $name  Playlist name.
     * @param  string|null  $description  Playlist description.
     * @param  bool|null  $public  Whether the playlist is public.
     * @param  bool|null  $collaborative  Whether the playlist is collaborative.
     * @return Response The HTTP response containing the created playlist.
     */
    public function createPlaylist(string $userId, string $name, ?string $description = null, ?bool $public = null, ?bool $collaborative = null): Response
    {
        $data = array_filter([
            'name' => $name,
            'description' => $description,
            'public' => $public,
            'collaborative' => $collaborative,
        ], fn ($value) => $value !== null);

        return $this->makeRequest('post', '/users/'.$userId.'/playlists', $data);
    }

    /**
     * Add items to a playlist.
     *
     * @param  string  $playlistId  Playlist ID.
     * @param  string|array<string>  $uris  Spotify URIs to add.
     * @param  int|null  $position  Position to insert the items (default: null, appends to end).
     * @return Response The HTTP response containing a snapshot_id.
     */
    public function addItemsToPlaylist(string $playlistId, string|array $uris, ?int $position = null): Response
    {
        $data = array_filter([
            'uris' => $this->normalizeToArray($uris),
            'position' => $position,
        ], fn ($value) => $value !== null);

        return $this->makeRequest('post', '/playlists/'.$playlistId.'/tracks', $data);
    }

    /**
     * Remove items from a playlist.
     *
     * @param  string  $playlistId  Playlist ID.
     * @param  string|array<string>  $uris  Spotify URIs to remove.
     * @return Response The HTTP response containing a snapshot_id.
     */
    public function removePlaylistItems(string $playlistId, string|array $uris): Response
    {
        $tracks = collect($this->normalizeToArray($uris))
            ->map(fn ($uri) => ['uri' => $uri])
            ->toArray();

        return $this->makeRequest('delete', '/playlists/'.$playlistId.'/tracks', [
            'tracks' => $tracks,
        ]);
    }

    /**
     * Update playlist details.
     *
     * @param  string  $playlistId  Playlist ID.
     * @param  string|null  $name  New playlist name.
     * @param  string|null  $description  New playlist description.
     * @param  bool|null  $public  Whether the playlist is public.
     * @param  bool|null  $collaborative  Whether the playlist is collaborative.
     * @return Response The HTTP response.
     */
    public function updatePlaylistDetails(string $playlistId, ?string $name = null, ?string $description = null, ?bool $public = null, ?bool $collaborative = null): Response
    {
        $data = array_filter([
            'name' => $name,
            'description' => $description,
            'public' => $public,
            'collaborative' => $collaborative,
        ], fn ($value) => $value !== null);

        return $this->makeRequest('put', '/playlists/'.$playlistId, $data);
    }

    /**
     * Reorder items in a playlist.
     *
     * @param  string  $playlistId  Playlist ID.
     * @param  int  $rangeStart  Position of the first item to be reordered.
     * @param  int  $insertBefore  Position where the items should be inserted.
     * @param  int|null  $rangeLength  Number of items to be reordered (default: 1).
     * @param  string|null  $snapshotId  Playlist snapshot ID.
     * @return Response The HTTP response containing a snapshot_id.
     */
    public function reorderPlaylistItems(string $playlistId, int $rangeStart, int $insertBefore, ?int $rangeLength = null, ?string $snapshotId = null): Response
    {
        $data = array_filter([
            'range_start' => $rangeStart,
            'insert_before' => $insertBefore,
            'range_length' => $rangeLength,
            'snapshot_id' => $snapshotId,
        ], fn ($value) => $value !== null);

        return $this->makeRequest('put', '/playlists/'.$playlistId.'/tracks', $data);
    }

    // Search

    /**
     * Search for items on Spotify.
     *
     * @param  string  $query  Search query.
     * @param  array<string>  $types  Types to search for (album, artist, playlist, track, show, episode, audiobook).
     * @param  string|null  $market  Market to retrieve content for (default: null).
     * @param  int  $limit  Maximum number of items to return (default: 20).
     * @param  int  $offset  Index of the first item to return (default: 0).
     * @param  string|null  $include_external  Include external content (audio) (default: null).
     * @return Response The HTTP response containing search results.
     */
    public function searchForItem(string $query, array $types, ?string $market = null, int $limit = 20, int $offset = 0, ?string $include_external = null): Response
    {
        $limit = $this->clampLimit($limit);

        return $this->makeRequest('get', '/search', [
            'q' => $query,
            'type' => implode(',', $types),
            'market' => $market,
            'limit' => $limit,
            'offset' => $offset,
            'include_external' => $include_external,
        ]);
    }

    // Shows

    /**
     * Get a specific show by ID.
     *
     * @param  string  $showId  Show ID.
     * @param  string|null  $market  Market to retrieve content for (default: null).
     * @return Response The HTTP response containing show data.
     */
    public function getShow(string $showId, ?string $market = null): Response
    {
        return $this->makeRequest('get', '/shows/'.$showId, [
            'market' => $market,
        ]);
    }

    /**
     * Get several shows by their IDs.
     *
     * @param  string|array<string>  $showIds  Show IDs (comma-separated string or array).
     * @param  string|null  $market  Market to retrieve content for (default: null).
     * @return Response The HTTP response containing show data.
     */
    public function getSeveralShows(string|array $showIds, ?string $market = null): Response
    {
        return $this->makeRequest('get', '/shows', [
            'ids' => $this->normalizeToCommaSeparated($showIds),
            'market' => $market,
        ]);
    }

    /**
     * Get episodes from a specific show.
     *
     * @param  string  $showId  Show ID.
     * @param  string|null  $market  Market to retrieve content for (default: null).
     * @param  int  $limit  Maximum number of items to return (default: 20).
     * @param  int  $offset  Index of the first item to return (default: 0).
     * @return Response The HTTP response containing show episodes.
     */
    public function getShowEpisodes(string $showId, ?string $market = null, int $limit = 20, int $offset = 0): Response
    {
        $limit = $this->clampLimit($limit);

        return $this->makeRequest('get', '/shows/'.$showId.'/episodes', [
            'market' => $market,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get the current user's saved shows.
     *
     * @param  int  $limit  Maximum number of items to return (default: 20).
     * @param  int  $offset  Index of the first item to return (default: 0).
     * @return Response The HTTP response containing saved shows.
     */
    public function getUsersSavedShows(int $limit = 20, int $offset = 0): Response
    {
        $limit = $this->clampLimit($limit);

        return $this->makeRequest('get', '/me/shows', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Check if one or more shows are saved in the user's library.
     *
     * @param  string|array<string>  $showIds  Show IDs to check (comma-separated string or array).
     * @return Response The HTTP response containing boolean array indicating if shows are saved.
     */
    public function checkUsersSavedShows(string|array $showIds): Response
    {
        return $this->makeRequest('get', '/me/shows/contains', [
            'ids' => $this->normalizeToCommaSeparated($showIds),
        ]);
    }

    /**
     * Save one or more shows to the current user's library.
     *
     * @param  string|array<string>  $showIds  Show IDs to save (comma-separated string or array).
     * @return Response The HTTP response.
     */
    public function saveShowsForCurrentUser(string|array $showIds): Response
    {
        return $this->makeRequest('put', '/me/shows', [
            'ids' => $this->normalizeToArray($showIds),
        ]);
    }

    /**
     * Remove one or more shows from the current user's library.
     *
     * @param  string|array<string>  $showIds  Show IDs to remove (comma-separated string or array).
     * @return Response The HTTP response.
     */
    public function removeUsersSavedShows(string|array $showIds): Response
    {
        return $this->makeRequest('delete', '/me/shows', [
            'ids' => $this->normalizeToArray($showIds),
        ]);
    }

    // Tracks

    /**
     * Get a specific track by ID.
     *
     * @param  string  $trackId  Track ID.
     *
     * @throws Exception
     */
    public function getTrack(string $trackId, ?string $market = null): Response
    {
        return $this->makeRequest('get', '/tracks/'.$trackId, [
            'market' => $market,
        ]);
    }

    /**
     * Get several tracks by their IDs.
     *
     * @param  string|array<string>  $trackIds  Track IDs (comma-separated string or array).
     * @param  string|null  $market  Market to retrieve content for (default: null).
     * @return Response The HTTP response containing track data.
     */
    public function getSeveralTracks(string|array $trackIds, ?string $market = null): Response
    {
        return $this->makeRequest('get', '/tracks', [
            'ids' => $this->normalizeToCommaSeparated($trackIds),
            'market' => $market,
        ]);
    }

    /**
     * Get the current user's saved tracks.
     *
     * @param  string|null  $market  Market to retrieve content for (default: null).
     * @param  int  $limit  Maximum number of items to return (default: 20).
     * @param  int  $offset  Index of the first item to return (default: 0).
     * @return Response The HTTP response containing saved tracks.
     */
    public function getUsersSavedTracks(?string $market = null, int $limit = 20, int $offset = 0): Response
    {
        $limit = $this->clampLimit($limit);

        return $this->makeRequest('get', '/me/tracks', [
            'market' => $market,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Check if one or more tracks are saved in the user's library.
     *
     * @param  string|array<string>  $trackIds  Track IDs to check (comma-separated string or array).
     * @return Response The HTTP response containing boolean array indicating if tracks are saved.
     */
    public function checkUsersSavedTracks(string|array $trackIds): Response
    {
        return $this->makeRequest('get', '/me/tracks/contains', [
            'ids' => $this->normalizeToCommaSeparated($trackIds),
        ]);
    }

    /**
     * Save one or more tracks to the current user's 'Your Music' library.
     *
     * @param  string|array<string>  $trackIds  Track IDs to save (comma-separated string or array).
     * @return Response The HTTP response.
     */
    public function saveTracksForCurrentUser(string|array $trackIds): Response
    {
        return $this->makeRequest('put', '/me/tracks', [
            'ids' => $this->normalizeToArray($trackIds),
        ]);
    }

    /**
     * Remove one or more tracks from the current user's 'Your Music' library.
     *
     * @param  string|array<string>  $trackIds  Track IDs to remove (comma-separated string or array).
     * @return Response The HTTP response.
     */
    public function removeUsersSavedTracks(string|array $trackIds): Response
    {
        return $this->makeRequest('delete', '/me/tracks', [
            'ids' => $this->normalizeToArray($trackIds),
        ]);
    }

    // Users
    /**
     * Get the current user's profile.
     *
     * @return Response The HTTP response containing user profile data.
     */
    public function getCurrentUsersProfile(): Response
    {
        return $this->makeRequest('get', '/me');
    }

    /**
     * Get a user's profile.
     *
     * @param  string  $userId  User ID.
     * @return Response The HTTP response containing user profile data.
     */
    public function getUsersProfile(string $userId): Response
    {
        return $this->makeRequest('get', '/users/'.$userId);
    }

    /**
     * Get the user's followed artists.
     *
     * @param  string|null  $after  Artist ID to start after (default: null).
     * @param  int  $limit  Maximum number of items to return (default: 20).
     * @return Response The HTTP response containing followed artists.
     */
    public function getFollowedArtists(?string $after = null, int $limit = 20): Response
    {
        $limit = $this->clampLimit($limit);

        return $this->makeRequest('get', '/me/following', [
            'type' => 'artist',
            'after' => $after,
            'limit' => $limit,
        ]);
    }

    /**
     * Check if the user follows artists or other users.
     *
     * @param  string|array<string>  $ids  Artist or user IDs to check (comma-separated string or array).
     * @param  string  $type  Type to check (artist or user) (default: artist).
     * @return Response The HTTP response containing boolean array indicating if user follows the items.
     */
    public function checkIfUserFollowsArtistsOrUsers(string|array $ids, string $type = 'artist'): Response
    {
        return $this->makeRequest('get', '/me/following/contains', [
            'type' => $type,
            'ids' => $this->normalizeToCommaSeparated($ids),
        ]);
    }

    /**
     * Check if the current user follows a playlist.
     *
     * @param  string  $playlistId  Playlist ID.
     * @param  string|array<string>  $ids  User IDs to check (comma-separated string or array).
     * @return Response The HTTP response containing boolean array indicating if users follow the playlist.
     */
    public function checkIfCurrentUserFollowsPlaylist(string $playlistId, string|array $ids): Response
    {
        return $this->makeRequest('get', '/playlists/'.$playlistId.'/followers/contains', [
            'ids' => $this->normalizeToCommaSeparated($ids),
        ]);
    }

    /**
     * Get the current user's top tracks or artists.
     *
     * @param  SpotifyTopType  $type  Type of data to fetch: tracks or artists.
     * @param  SpotifyTimeRange  $timeRange  Time range (default: medium_term).
     * @param  int  $limit  Maximum number of items to return (default: 20).
     *
     * @throws Exception
     */
    public function getUserTop(SpotifyTopType $type, SpotifyTimeRange $timeRange = SpotifyTimeRange::MEDIUM_TERM, int $limit = 20): Response
    {
        $limit = $this->clampLimit($limit);

        return $this->makeRequest('get', '/me/top/'.$type->value, [
            'limit' => $limit,
            'time_range' => $timeRange->value,
        ]);
    }

    /**
     * Get the current user's top tracks.
     *
     * @param  SpotifyTimeRange|string  $timeRange  Time range (default: medium_term).
     * @param  int  $limit  Maximum number of items to return (default: 20).
     *
     * @throws Exception
     */
    public function getUserTopTracks(SpotifyTimeRange|string $timeRange = SpotifyTimeRange::MEDIUM_TERM, int $limit = 20): Response
    {
        if (is_string($timeRange)) {
            $timeRange = SpotifyTimeRange::fromString($timeRange);
        }

        return $this->getUserTop(SpotifyTopType::TRACKS, $timeRange, $limit);
    }

    /**
     * Get the current user's top artists.
     *
     * @param  SpotifyTimeRange|string  $timeRange  Time range (default: medium_term).
     * @param  int  $limit  Maximum number of items to return (default: 20).
     *
     * @throws Exception
     */
    public function getUserTopArtists(SpotifyTimeRange|string $timeRange = SpotifyTimeRange::MEDIUM_TERM, int $limit = 20): Response
    {
        if (is_string($timeRange)) {
            $timeRange = SpotifyTimeRange::fromString($timeRange);
        }

        return $this->getUserTop(SpotifyTopType::ARTISTS, $timeRange, $limit);
    }

    /**
     * Get the currently authenticated Spotify user.
     *
     * @return Response The HTTP response containing authenticated user data.
     */
    public function getAuthenticatedUser(): Response
    {
        return $this->getCurrentUsersProfile();
    }

    /**
     * @param  string|array<string>  $trackIds
     * @return array<string>
     */
    protected function formatForTracksPlayback(string|array $trackIds): array
    {
        return collect($this->normalizeToArray($trackIds))->map(fn ($track) => 'spotify:track:'.$track)->toArray();
    }
}
