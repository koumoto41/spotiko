<?php

use App\Infrastructure\Persistence\Eloquent\Models\Album;
use App\Infrastructure\Persistence\Eloquent\Models\Artist;
use App\Infrastructure\Persistence\Eloquent\Models\TopArtist;
use App\Infrastructure\Persistence\Eloquent\Models\TopTrack;
use App\Infrastructure\Persistence\Eloquent\Models\Track;
use App\Infrastructure\Persistence\Eloquent\Models\User;

test('未認証のリクエストは拒否される', function () {
    $this->getJson('/api/rankings')->assertUnauthorized();
});

test('まだ集計データが無い場合は集計日null・空配列を返す', function () {
    $this->actingAs(User::factory()->create());

    $this->getJson('/api/rankings')
        ->assertOk()
        ->assertExactJson([
            'artists' => ['aggregate_date' => null, 'items' => []],
            'tracks' => ['aggregate_date' => null, 'items' => []],
        ]);
});

test('最新日のアーティスト・トラックのランキングを返す', function () {
    $this->actingAs(User::factory()->create());

    $artist = Artist::create([
        'spotify_id' => 'artist-1',
        'name' => 'Artist One',
        'url' => 'https://open.spotify.com/artist/artist-1',
        'popularity' => 90,
        'img_640' => '',
        'img_320' => '',
        'img_160' => '',
    ]);
    $album = Album::create([
        'spotify_id' => 'album-1',
        'artist_id' => $artist->id,
        'name' => 'Album One',
        'url' => 'https://open.spotify.com/album/album-1',
        'release_date' => '2020-01-01',
        'total_track' => 10,
        'type' => 'album',
        'img_640' => '',
        'img_300' => '',
        'img_64' => '',
    ]);
    $track = Track::create([
        'spotify_id' => 'track-1',
        'artist_id' => $artist->id,
        'album_id' => $album->id,
        'name' => 'Track One',
        'url' => 'https://open.spotify.com/track/track-1',
        'popularity' => 80,
        'track_number' => 1,
        'preview_url' => '',
    ]);

    $today = now()->toDateString();
    TopArtist::create(['target_date' => $today, 'artist_id' => $artist->id, 'point' => 50, 'popularity' => 90]);
    TopTrack::create([
        'target_date' => $today,
        'artist_id' => $artist->id,
        'album_id' => $album->id,
        'track_id' => $track->id,
        'point' => 50,
        'popularity' => 80,
    ]);

    $this->getJson('/api/rankings')
        ->assertOk()
        ->assertJsonPath('artists.aggregate_date', $today)
        ->assertJsonPath('artists.items.0.name', 'Artist One')
        ->assertJsonPath('tracks.aggregate_date', $today)
        ->assertJsonPath('tracks.items.0.track_name', 'Track One')
        ->assertJsonPath('tracks.items.0.artist_name', 'Artist One')
        ->assertJsonPath('tracks.items.0.album_name', 'Album One');
});
