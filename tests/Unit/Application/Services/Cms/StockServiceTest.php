<?php

use App\Application\Services\Cms\StockService;
use App\Domain\Album\Repositories\AlbumRepositoryInterface;
use App\Domain\Artist\Repositories\ArtistRepositoryInterface;
use App\Domain\ArtistGenre\Repositories\ArtistGenreRepositoryInterface;
use App\Domain\ArtistGenreMap\Repositories\ArtistGenreMapRepositoryInterface;
use App\Domain\MyAlbum\Repositories\MyAlbumRepositoryInterface;
use App\Domain\Spotify\SpotifyClientInterface;

beforeEach(function () {
    $this->spotifyClient = Mockery::mock(SpotifyClientInterface::class);
    $this->myAlbumRepository = Mockery::mock(MyAlbumRepositoryInterface::class);
    $this->albumRepository = Mockery::mock(AlbumRepositoryInterface::class);
    $this->artistRepository = Mockery::mock(ArtistRepositoryInterface::class);
    $this->artistGenreRepository = Mockery::mock(ArtistGenreRepositoryInterface::class);
    $this->artistGenreMapRepository = Mockery::mock(ArtistGenreMapRepositoryInterface::class);

    $this->service = new StockService(
        $this->spotifyClient,
        $this->myAlbumRepository,
        $this->albumRepository,
        $this->artistRepository,
        $this->artistGenreRepository,
        $this->artistGenreMapRepository,
    );
});

test('registerMyAlbum は未登録のアーティストとアルバムをSpotifyから取得して登録する', function () {
    $this->artistRepository->shouldReceive('existArtist')->once()->with('artist-1')->andReturnFalse();
    $this->spotifyClient->shouldReceive('getArtistById')->once()->with(1, 'artist-1')
        ->andReturn(fakeSpotifyArtist(['id' => 'artist-1']));
    $this->artistRepository->shouldReceive('upsertArtist')->once();
    $this->artistGenreRepository->shouldReceive('upsertGenre')->once();
    $this->artistGenreRepository->shouldReceive('getGenreIdByGenreName')->andReturn(5);
    $this->artistGenreMapRepository->shouldReceive('firstOrCreateArtistGenreMap');

    $this->artistRepository->shouldReceive('getArtistIdBySpotifyId')->with('artist-1')->andReturn(11);

    $this->albumRepository->shouldReceive('existAlbum')->once()->with('album-1')->andReturnFalse();
    $this->spotifyClient->shouldReceive('getAlbumById')->once()->with(1, 'album-1')
        ->andReturn(fakeSpotifyAlbum(['id' => 'album-1']));
    $this->albumRepository->shouldReceive('insertAlbum')->once();
    $this->albumRepository->shouldReceive('getAlbumIdBySpotifyId')->with('album-1')->andReturn(22);

    $this->myAlbumRepository->shouldReceive('existMyAlbum')->once()->with(22)->andReturnFalse();
    $this->myAlbumRepository->shouldReceive('insertMyAlbum')->once()->with(['album_id' => 22]);

    $result = $this->service->registerMyAlbum('artist-1', 'album-1');

    expect($result)->toBe(['processing' => true, 'message' => '登録が完了しました']);
});

test('registerMyAlbum はアーティストとアルバムが既知の場合Spotifyへの問い合わせをスキップする', function () {
    $this->artistRepository->shouldReceive('existArtist')->once()->with('artist-1')->andReturnTrue();
    $this->artistRepository->shouldReceive('getArtistIdBySpotifyId')->with('artist-1')->andReturn(11);
    $this->albumRepository->shouldReceive('existAlbum')->once()->with('album-1')->andReturnTrue();
    $this->albumRepository->shouldReceive('getAlbumIdBySpotifyId')->with('album-1')->andReturn(22);

    $this->spotifyClient->shouldNotReceive('getArtistById');
    $this->spotifyClient->shouldNotReceive('getAlbumById');

    $this->myAlbumRepository->shouldReceive('existMyAlbum')->once()->with(22)->andReturnFalse();
    $this->myAlbumRepository->shouldReceive('insertMyAlbum')->once()->with(['album_id' => 22]);

    $result = $this->service->registerMyAlbum('artist-1', 'album-1');

    expect($result['processing'])->toBeTrue();
});

test('registerMyAlbum は登録済みのアルバムを重複登録せずそのまま報告する', function () {
    $this->artistRepository->shouldReceive('existArtist')->andReturnTrue();
    $this->artistRepository->shouldReceive('getArtistIdBySpotifyId')->andReturn(11);
    $this->albumRepository->shouldReceive('existAlbum')->andReturnTrue();
    $this->albumRepository->shouldReceive('getAlbumIdBySpotifyId')->andReturn(22);

    $this->myAlbumRepository->shouldReceive('existMyAlbum')->once()->with(22)->andReturnTrue();
    $this->myAlbumRepository->shouldNotReceive('insertMyAlbum');

    $result = $this->service->registerMyAlbum('artist-1', 'album-1');

    expect($result)->toBe(['processing' => false, 'message' => 'すでに登録されています']);
});
