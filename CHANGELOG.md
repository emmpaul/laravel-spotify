# Changelog

All notable changes to `laravel-spotify` will be documented in this file.

## Unreleased

### Added

- `createPlaylist()` - Create a playlist for a Spotify user
- `addItemsToPlaylist()` - Add items to a playlist
- `removePlaylistItems()` - Remove items from a playlist
- `updatePlaylistDetails()` - Update a playlist's name, description, and visibility
- `reorderPlaylistItems()` - Reorder items in a playlist
- Support for DELETE requests with JSON body in `makeRequest`

## release v1.2.0 - 2026-02-11

### What's Changed

* Fixes avatar issue + better logging by @shahzeb1 in https://github.com/emmpaul/laravel-spotify/pull/5
* build(deps): bump actions/checkout from 5 to 6 by @dependabot[bot] in https://github.com/emmpaul/laravel-spotify/pull/3
* build(deps): bump stefanzweifel/git-auto-commit-action from 6 to 7 by @dependabot[bot] in https://github.com/emmpaul/laravel-spotify/pull/2

### New Contributors

* @shahzeb1 made their first contribution in https://github.com/emmpaul/laravel-spotify/pull/5

**Full Changelog**: https://github.com/emmpaul/laravel-spotify/compare/v1.1.0...v1.2.0

## Release v1.1.0 - 2026-02-11

### What's Changed

* build(deps): bump dependabot/fetch-metadata from 2.4.0 to 2.5.0 by @dependabot[bot] in https://github.com/emmpaul/laravel-spotify/pull/8
* fix: clamp limit parameter to 1-50 on API methods by @emmpaul in https://github.com/emmpaul/laravel-spotify/pull/10
* fix: set token expiry on user creation by @emmpaul in https://github.com/emmpaul/laravel-spotify/pull/9

### New Contributors

* @emmpaul made their first contribution in https://github.com/emmpaul/laravel-spotify/pull/10

**Full Changelog**: https://github.com/emmpaul/laravel-spotify/compare/v1.0.0...v1.1.0

## v1.0.0 - 2025-09-08

### First release (v1.0.0)

#### What's Changed

* Bump actions/checkout from 4 to 5 by @dependabot[bot] in https://github.com/emmpaul/laravel-spotify/pull/1

#### New Contributors

* @dependabot[bot] made their first contribution in https://github.com/emmpaul/laravel-spotify/pull/1

**Full Changelog**: https://github.com/emmpaul/laravel-spotify/commits/v1.0.0
