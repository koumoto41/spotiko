<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature tests boot the full Laravel application (Tests\TestCase) and get
| a fresh in-memory SQLite schema per test via RefreshDatabase.
|
| Unit tests intentionally do NOT extend Tests\TestCase — they exercise
| Application/UseCase and Application/Service classes directly with mocked
| Domain repository interfaces, with no framework boot and no database.
|
*/

uses(TestCase::class, RefreshDatabase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Shared Spotify API response fixtures
|--------------------------------------------------------------------------
|
| jwilsson/spotify-web-api-php decodes JSON responses as plain stdClass
| objects (not typed DTOs), so these helpers build the same shape by hand
| for use in Application-layer unit tests.
|
*/

function fakeSpotifyArtist(array $overrides = []): stdClass
{
    return (object) array_merge([
        'id' => 'artist-spotify-id',
        'name' => 'Test Artist',
        'external_urls' => (object) ['spotify' => 'https://open.spotify.com/artist/artist-spotify-id'],
        'popularity' => 77,
        'images' => [
            (object) ['height' => '640', 'url' => 'https://img.test/640.jpg'],
            (object) ['height' => '320', 'url' => 'https://img.test/320.jpg'],
            (object) ['height' => '160', 'url' => 'https://img.test/160.jpg'],
            (object) ['height' => '64', 'url' => 'https://img.test/64.jpg'],
        ],
        'genres' => ['j-pop', 'rock'],
    ], $overrides);
}

function fakeSpotifyAlbum(array $overrides = []): stdClass
{
    return (object) array_merge([
        'id' => 'album-spotify-id',
        'name' => 'Test Album',
        'external_urls' => (object) ['spotify' => 'https://open.spotify.com/album/album-spotify-id'],
        'release_date' => '2020-01-01',
        'total_tracks' => 10,
        'album_type' => 'album',
        'images' => [
            (object) ['height' => '640', 'url' => 'https://img.test/album-640.jpg'],
            (object) ['height' => '300', 'url' => 'https://img.test/album-300.jpg'],
            (object) ['height' => '64', 'url' => 'https://img.test/album-64.jpg'],
        ],
    ], $overrides);
}

function fakeSpotifyTrack(array $overrides = []): stdClass
{
    return (object) array_merge([
        'id' => 'track-spotify-id',
        'name' => 'Test Track',
        'external_urls' => (object) ['spotify' => 'https://open.spotify.com/track/track-spotify-id'],
        'popularity' => 55,
        'track_number' => 3,
        'preview_url' => 'https://preview.test/track.mp3',
        'artists' => [(object) ['id' => 'artist-spotify-id']],
        'album' => (object) ['id' => 'album-spotify-id'],
    ], $overrides);
}
