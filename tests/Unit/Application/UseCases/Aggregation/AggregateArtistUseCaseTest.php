<?php

use App\Application\Services\AggregateService;
use App\Application\UseCases\Aggregation\AggregateArtistUseCase;
use App\Domain\Album\Repositories\AlbumRepositoryInterface;
use App\Domain\Artist\Repositories\ArtistRepositoryInterface;
use App\Domain\ArtistGenre\Repositories\ArtistGenreRepositoryInterface;
use App\Domain\ArtistGenreMap\Repositories\ArtistGenreMapRepositoryInterface;
use App\Domain\Spotify\SpotifyClientInterface;
use App\Domain\TopArtist\Repositories\TopArtistRepositoryInterface;
use App\Domain\TopTrack\Repositories\TopTrackRepositoryInterface;
use App\Domain\Track\Repositories\TrackRepositoryInterface;

/*
|--------------------------------------------------------------------------
| モック方針についての補足
|--------------------------------------------------------------------------
|
| AggregateService は `final` クラスなので Mockery でプロキシモックできない。
| かつ自身はI/Oを持たず、コンストラクタで受け取ったDomain interfaceに全て委譲
| している。そのため、ここではユースケースと同じモック済みinterfaceで実体の
| AggregateService を組み立てて注入し、真のモック境界をDomain層に置いている。
*/

beforeEach(function () {
    $this->spotifyClient = Mockery::mock(SpotifyClientInterface::class);
    $this->artistRepository = Mockery::mock(ArtistRepositoryInterface::class);
    $this->albumRepository = Mockery::mock(AlbumRepositoryInterface::class);
    $this->trackRepository = Mockery::mock(TrackRepositoryInterface::class);
    $this->artistGenreRepository = Mockery::mock(ArtistGenreRepositoryInterface::class);
    $this->artistGenreMapRepository = Mockery::mock(ArtistGenreMapRepositoryInterface::class);
    $this->topArtistRepository = Mockery::mock(TopArtistRepositoryInterface::class);
    $this->topTrackRepository = Mockery::mock(TopTrackRepositoryInterface::class);

    $this->aggregateService = new AggregateService(
        $this->spotifyClient,
        $this->artistRepository,
        $this->albumRepository,
        $this->trackRepository,
        $this->artistGenreRepository,
        $this->artistGenreMapRepository,
        $this->topArtistRepository,
        $this->topTrackRepository,
    );

    $this->useCase = new AggregateArtistUseCase(
        $this->spotifyClient,
        $this->aggregateService,
        $this->artistRepository,
        $this->artistGenreRepository,
    );
});

test('トップアーティストを取得し、登録してランキングを付与する', function () {
    $response = (object) ['items' => [fakeSpotifyArtist(['id' => 'a1', 'popularity' => 90])]];

    $this->spotifyClient->shouldReceive('getMyTop')->once()->with(1, 'artists')->andReturn($response);

    $this->artistRepository->shouldReceive('upsertArtist')->once();
    $this->artistGenreRepository->shouldReceive('upsertGenre')->once();
    $this->artistRepository->shouldReceive('getArtistIdBySpotifyId')->with('a1')->andReturn(101);
    $this->artistGenreRepository->shouldReceive('getGenreIdByGenreName')->andReturn(5);
    $this->artistGenreMapRepository->shouldReceive('firstOrCreateArtistGenreMap');

    $this->topArtistRepository->shouldReceive('existTargetDateData')->once()->andReturnFalse();
    $this->topArtistRepository->shouldReceive('insertTopArtist')->once()->with(date('Y-m-d'), 101, 90, 50);

    ($this->useCase)(1);
});

test('トークンリフレッシュ可能なエラーの場合は1回だけリトライし、それでも失敗したら諦める', function () {
    $exception = new Exception('The access token expired', 401);

    $this->spotifyClient->shouldReceive('getMyTop')->twice()->with(1, 'artists')->andThrow($exception);
    $this->spotifyClient->shouldReceive('checkRefreshToken')
        ->twice()
        ->with($exception, 1)
        ->andReturn(true, false);

    // 全ての試行が失敗した場合、Repositoryへの書き込みは一切発生しない
    $this->artistRepository->shouldNotReceive('upsertArtist');
    $this->topArtistRepository->shouldNotReceive('insertTopArtist');

    ($this->useCase)(1);
});

test('リフレッシュ不可能なエラーの場合は即座に諦める', function () {
    $exception = new Exception('Some other Spotify error', 500);

    $this->spotifyClient->shouldReceive('getMyTop')->once()->with(1, 'artists')->andThrow($exception);
    $this->spotifyClient->shouldReceive('checkRefreshToken')->once()->with($exception, 1)->andReturnFalse();

    $this->artistRepository->shouldNotReceive('upsertArtist');

    ($this->useCase)(1);
});
