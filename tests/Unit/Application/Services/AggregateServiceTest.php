<?php

use App\Application\Services\AggregateService;
use App\Domain\Album\Repositories\AlbumRepositoryInterface;
use App\Domain\Artist\Repositories\ArtistRepositoryInterface;
use App\Domain\ArtistGenre\Repositories\ArtistGenreRepositoryInterface;
use App\Domain\ArtistGenreMap\Repositories\ArtistGenreMapRepositoryInterface;
use App\Domain\Spotify\SpotifyClientInterface;
use App\Domain\TopArtist\Repositories\TopArtistRepositoryInterface;
use App\Domain\TopTrack\Repositories\TopTrackRepositoryInterface;
use App\Domain\Track\Repositories\TrackRepositoryInterface;

beforeEach(function () {
    $this->spotifyClient = Mockery::mock(SpotifyClientInterface::class);
    $this->artistRepository = Mockery::mock(ArtistRepositoryInterface::class);
    $this->albumRepository = Mockery::mock(AlbumRepositoryInterface::class);
    $this->trackRepository = Mockery::mock(TrackRepositoryInterface::class);
    $this->artistGenreRepository = Mockery::mock(ArtistGenreRepositoryInterface::class);
    $this->artistGenreMapRepository = Mockery::mock(ArtistGenreMapRepositoryInterface::class);
    $this->topArtistRepository = Mockery::mock(TopArtistRepositoryInterface::class);
    $this->topTrackRepository = Mockery::mock(TopTrackRepositoryInterface::class);

    $this->service = new AggregateService(
        $this->spotifyClient,
        $this->artistRepository,
        $this->albumRepository,
        $this->trackRepository,
        $this->artistGenreRepository,
        $this->artistGenreMapRepository,
        $this->topArtistRepository,
        $this->topTrackRepository,
    );
});

// --- convertMyTopArtistToArrayBySpotifyApi -------------------------------

test('convertMyTopArtistToArrayBySpotifyApi は getMyTop の items レスポンスを配列に変換する', function () {
    $response = (object) [
        'items' => [
            fakeSpotifyArtist(['id' => 'artist-1', 'name' => 'Artist One']),
            fakeSpotifyArtist(['id' => 'artist-2', 'name' => 'Artist Two']),
        ],
    ];

    $result = $this->service->convertMyTopArtistToArrayBySpotifyApi($response);

    expect($result)->toHaveCount(2);
    expect($result[0]['artist'])->toMatchArray([
        'spotify_id' => 'artist-1',
        'name' => 'Artist One',
        'popularity' => 77,
        'img_640' => 'https://img.test/640.jpg',
        'img_320' => 'https://img.test/320.jpg',
        'img_160' => 'https://img.test/160.jpg',
    ]);
    // 許可サイズ外(64px)の画像は含まれないこと
    expect($result[0]['artist'])->not->toHaveKey('img_64');
    expect($result[0]['genre'])->toBe(['j-pop', 'rock']);
});

test('convertMyTopArtistToArrayBySpotifyApi は items でラップされていない単体のartistオブジェクトも受け付ける', function () {
    $artist = fakeSpotifyArtist(['id' => 'solo-artist']);

    $result = $this->service->convertMyTopArtistToArrayBySpotifyApi($artist);

    expect($result)->toHaveCount(1);
    expect($result[0]['artist']['spotify_id'])->toBe('solo-artist');
});

// --- putArtistGenreArray --------------------------------------------------

test('putArtistGenreArray はアーティスト間でジャンルを重複排除し、ジャンルの無いアーティストはスキップする', function () {
    $artistArray = [
        ['artist' => ['spotify_id' => 'a1'], 'genre' => ['pop', 'rock']],
        ['artist' => ['spotify_id' => 'a2'], 'genre' => ['rock', 'jazz']],
        ['artist' => ['spotify_id' => 'a3']],
    ];

    $result = $this->service->putArtistGenreArray($artistArray);

    expect($result)->toBe(['pop', 'rock', 'jazz']);
});

// --- decideTopArtist -------------------------------------------------------

test('decideTopArtist は同日分のデータを置き換え、入力順にポイントを割り振る', function () {
    $artistArray = [
        ['artist' => ['spotify_id' => 'a1', 'popularity' => 90]],
        ['artist' => ['spotify_id' => 'a2', 'popularity' => 80]],
    ];
    $today = date('Y-m-d');

    $this->topArtistRepository->shouldReceive('existTargetDateData')->once()->with($today)->andReturnTrue();
    $this->topArtistRepository->shouldReceive('deleteTopArtistByTargetDate')->once()->with($today);

    $this->artistRepository->shouldReceive('getArtistIdBySpotifyId')->once()->with('a1')->andReturn(101);
    $this->artistRepository->shouldReceive('getArtistIdBySpotifyId')->once()->with('a2')->andReturn(102);

    $this->topArtistRepository->shouldReceive('insertTopArtist')->once()->with($today, 101, 90, 50);
    $this->topArtistRepository->shouldReceive('insertTopArtist')->once()->with($today, 102, 80, 49);

    $this->service->decideTopArtist($artistArray);
});

test('decideTopArtist はローカルに未登録のアーティストをスキップする', function () {
    $artistArray = [['artist' => ['spotify_id' => 'unknown', 'popularity' => 50]]];

    $this->topArtistRepository->shouldReceive('existTargetDateData')->andReturnFalse();
    $this->topArtistRepository->shouldNotReceive('deleteTopArtistByTargetDate');
    $this->artistRepository->shouldReceive('getArtistIdBySpotifyId')->with('unknown')->andReturnNull();
    $this->topArtistRepository->shouldNotReceive('insertTopArtist');

    $this->service->decideTopArtist($artistArray);
});

// --- convertMyTopTrackToArrayBySpotifyApi ----------------------------------

test('convertMyTopTrackToArrayBySpotifyApi はアーティストとアルバムがローカルに既存の場合Spotifyへの問い合わせをスキップする', function () {
    $response = (object) ['items' => [fakeSpotifyTrack(['id' => 'track-1'])]];

    $this->artistRepository->shouldReceive('existArtist')->once()->with('artist-spotify-id')->andReturnTrue();
    $this->artistRepository->shouldReceive('getArtistIdBySpotifyId')->with('artist-spotify-id')->andReturn(11);
    $this->albumRepository->shouldReceive('existAlbum')->once()->with('album-spotify-id')->andReturnTrue();
    $this->albumRepository->shouldReceive('getAlbumIdBySpotifyId')->with('album-spotify-id')->andReturn(22);

    $this->spotifyClient->shouldNotReceive('getArtistById');
    $this->spotifyClient->shouldNotReceive('getAlbumById');
    $this->artistRepository->shouldNotReceive('upsertArtist');
    $this->albumRepository->shouldNotReceive('insertAlbum');

    $result = $this->service->convertMyTopTrackToArrayBySpotifyApi($response, 1);

    expect($result[0]['track'])->toMatchArray([
        'spotify_id' => 'track-1',
        'name' => 'Test Track',
        'popularity' => 55,
        'artist_id' => 11,
        'album_id' => 22,
    ]);
});

test('convertMyTopTrackToArrayBySpotifyApi は未登録のアーティストとアルバムをSpotifyから取得して登録する', function () {
    $response = (object) ['items' => [fakeSpotifyTrack()]];

    $this->artistRepository->shouldReceive('existArtist')->once()->with('artist-spotify-id')->andReturnFalse();
    $this->spotifyClient->shouldReceive('getArtistById')->once()->with(1, 'artist-spotify-id')
        ->andReturn(fakeSpotifyArtist(['id' => 'artist-spotify-id']));
    $this->artistRepository->shouldReceive('upsertArtist')->once();
    $this->artistGenreRepository->shouldReceive('upsertGenre')->once();
    $this->artistRepository->shouldReceive('getArtistIdBySpotifyId')->with('artist-spotify-id')->andReturn(11);
    $this->artistGenreRepository->shouldReceive('getGenreIdByGenreName')->andReturn(5);
    $this->artistGenreMapRepository->shouldReceive('firstOrCreateArtistGenreMap');

    $this->albumRepository->shouldReceive('existAlbum')->once()->with('album-spotify-id')->andReturnFalse();
    $this->spotifyClient->shouldReceive('getAlbumById')->once()->with(1, 'album-spotify-id')
        ->andReturn(fakeSpotifyAlbum(['id' => 'album-spotify-id']));
    $this->albumRepository->shouldReceive('insertAlbum')->once();
    $this->albumRepository->shouldReceive('getAlbumIdBySpotifyId')->with('album-spotify-id')->andReturn(22);

    $result = $this->service->convertMyTopTrackToArrayBySpotifyApi($response, 1);

    expect($result[0]['track']['artist_id'])->toBe(11);
    expect($result[0]['track']['album_id'])->toBe(22);
});

// --- decideTopTrack ----------------------------------------------------------

test('decideTopTrack は同日分のデータを置き換え、入力順にポイントを割り振る', function () {
    $trackArray = [
        ['track' => ['spotify_id' => 't1', 'popularity' => 66, 'artist_id' => 11, 'album_id' => 22]],
    ];
    $today = date('Y-m-d');

    $this->topTrackRepository->shouldReceive('existTargetDateData')->once()->with($today)->andReturnFalse();
    $this->topTrackRepository->shouldNotReceive('deleteTopTrackByTargetDate');

    $this->trackRepository->shouldReceive('getTrackIdBySpotifyId')->once()->with('t1')->andReturn(303);
    $this->topTrackRepository->shouldReceive('insertTopTrack')->once()->with($today, 11, 22, 303, 66, 50);

    $this->service->decideTopTrack($trackArray);
});
