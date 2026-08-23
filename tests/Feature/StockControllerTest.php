<?php

use App\Domain\Spotify\SpotifyClientInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Album;
use App\Infrastructure\Persistence\Eloquent\Models\Artist;
use App\Infrastructure\Persistence\Eloquent\Models\MyAlbum;
use App\Infrastructure\Persistence\Eloquent\Models\User;

function seedArtistAndAlbum(): array
{
    $artist = Artist::create([
        'spotify_id' => 'artist-1',
        'name' => 'Artist One',
        'url' => 'https://open.spotify.com/artist/artist-1',
        'popularity' => 90,
        'img_640' => '', 'img_320' => '', 'img_160' => '',
    ]);
    $album = Album::create([
        'spotify_id' => 'album-1',
        'artist_id' => $artist->id,
        'name' => 'Album One',
        'url' => 'https://open.spotify.com/album/album-1',
        'release_date' => '2020-01-01',
        'total_track' => 10,
        'type' => 'album',
        'img_640' => '', 'img_300' => '', 'img_64' => '',
    ]);

    return [$artist, $album];
}

test('未認証のリクエストは拒否される', function () {
    $this->getJson('/api/stock')->assertUnauthorized();
    $this->getJson('/api/stock/search')->assertUnauthorized();
    $this->postJson('/api/stock', [])->assertUnauthorized();
});

test('登録済みアルバムの一覧を取得できる', function () {
    $this->actingAs(User::factory()->create());
    [, $album] = seedArtistAndAlbum();
    MyAlbum::create(['album_id' => $album->id]);

    $this->getJson('/api/stock')
        ->assertOk()
        ->assertJsonPath('count', 1)
        ->assertJsonPath('items.0.album_name', 'Album One')
        ->assertJsonPath('items.0.artist_name', 'Artist One');
});

test('検索クエリが空の場合は結果も空でSpotifyには問い合わせない', function () {
    $this->actingAs(User::factory()->create());

    $spotifyClient = Mockery::mock(SpotifyClientInterface::class);
    $spotifyClient->shouldNotReceive('searchItem');
    $this->app->instance(SpotifyClientInterface::class, $spotifyClient);

    $this->getJson('/api/stock/search')->assertOk()->assertExactJson(['items' => []]);
});

test('検索クエリがある場合はSpotifyクライアントに問い合わせる', function () {
    $this->actingAs(User::factory()->create());

    $searchResponse = (object) [
        'albums' => (object) [
            'items' => [
                (object) [
                    'name' => 'Found Album',
                    'id' => 'found-album',
                    'release_date' => '2021-01-01',
                    'type' => 'album',
                    'artists' => [(object) ['name' => 'Found Artist', 'id' => 'found-artist']],
                    'images' => [(object) ['url' => 'https://img.test/found.jpg']],
                ],
            ],
        ],
    ];

    $spotifyClient = Mockery::mock(SpotifyClientInterface::class);
    $spotifyClient->shouldReceive('searchItem')->once()->with(1, 'queen', 'album', 20, 0)->andReturn($searchResponse);
    $this->app->instance(SpotifyClientInterface::class, $spotifyClient);

    $this->getJson('/api/stock/search?query=queen')
        ->assertOk()
        ->assertJsonPath('items.0.album_name', 'Found Album')
        ->assertJsonPath('items.0.artist_name', 'Found Artist')
        ->assertJsonPath('items.0.is_register', false);
});

test('登録時のバリデーションはartist_idとalbum_idを必須とする', function () {
    $this->actingAs(User::factory()->create());

    $this->postJson('/api/stock', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['artist_id', 'album_id']);
});

test('既知のアーティスト・アルバムをストックへ登録できる', function () {
    $this->actingAs(User::factory()->create());
    seedArtistAndAlbum();

    $this->postJson('/api/stock', ['artist_id' => 'artist-1', 'album_id' => 'album-1'])
        ->assertCreated()
        ->assertJson(['processing' => true]);

    expect(MyAlbum::count())->toBe(1);
});

test('登録済みのアルバムは重複登録せずそのまま報告する', function () {
    $this->actingAs(User::factory()->create());
    [, $album] = seedArtistAndAlbum();
    MyAlbum::create(['album_id' => $album->id]);

    $this->postJson('/api/stock', ['artist_id' => 'artist-1', 'album_id' => 'album-1'])
        ->assertOk()
        ->assertJson(['processing' => false]);

    expect(MyAlbum::count())->toBe(1);
});
