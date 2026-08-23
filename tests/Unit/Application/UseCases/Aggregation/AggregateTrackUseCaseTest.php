<?php

use App\Application\Services\AggregateService;
use App\Application\UseCases\Aggregation\AggregateTrackUseCase;
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

    $this->useCase = new AggregateTrackUseCase(
        $this->spotifyClient,
        $this->aggregateService,
        $this->trackRepository,
    );
});

test('アーティスト・アルバムが既知の場合、トップトラックを取得し登録してランキングを付与する', function () {
    $response = (object) ['items' => [fakeSpotifyTrack(['id' => 't1', 'popularity' => 66])]];

    $this->spotifyClient->shouldReceive('getMyTop')->once()->with(1, 'tracks')->andReturn($response);

    $this->artistRepository->shouldReceive('existArtist')->with('artist-spotify-id')->andReturnTrue();
    $this->artistRepository->shouldReceive('getArtistIdBySpotifyId')->with('artist-spotify-id')->andReturn(11);
    $this->albumRepository->shouldReceive('existAlbum')->with('album-spotify-id')->andReturnTrue();
    $this->albumRepository->shouldReceive('getAlbumIdBySpotifyId')->with('album-spotify-id')->andReturn(22);

    $this->trackRepository->shouldReceive('upsertTrack')->once();
    $this->trackRepository->shouldReceive('getTrackIdBySpotifyId')->once()->with('t1')->andReturn(303);

    $this->topTrackRepository->shouldReceive('existTargetDateData')->once()->andReturnFalse();
    $this->topTrackRepository->shouldReceive('insertTopTrack')->once()->with(date('Y-m-d'), 11, 22, 303, 66, 50);

    ($this->useCase)(1);
});

test('トークンリフレッシュ可能なエラーの場合は1回だけリトライし、それでも失敗したら諦める', function () {
    $exception = new Exception('The access token expired', 401);

    $this->spotifyClient->shouldReceive('getMyTop')->twice()->with(1, 'tracks')->andThrow($exception);
    $this->spotifyClient->shouldReceive('checkRefreshToken')
        ->twice()
        ->with($exception, 1)
        ->andReturn(true, false);

    $this->trackRepository->shouldNotReceive('upsertTrack');

    ($this->useCase)(1);
});
