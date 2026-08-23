<?php

namespace App\Application\UseCases\Aggregation;

use App\Application\Services\AggregateService;
use App\Domain\Artist\Repositories\ArtistRepositoryInterface;
use App\Domain\ArtistGenre\Repositories\ArtistGenreRepositoryInterface;
use App\Domain\Spotify\SpotifyClientInterface;
use Exception;

final class AggregateArtistUseCase
{
    public function __construct(
        private SpotifyClientInterface $spotifyClient,
        private AggregateService $aggregateService,
        private ArtistRepositoryInterface $artistRepository,
        private ArtistGenreRepositoryInterface $artistGenreRepository
    ) {}

    public function __invoke(int $userId): void
    {
        try {
            $myTopArtist = $this->spotifyClient->getMyTop($userId, 'artists');
        } catch (Exception $e) {
            if ($this->spotifyClient->checkRefreshToken($e, $userId)) {
                $this->__invoke($userId);
            }

            return;
        }

        $myTopArtistArray = $this->aggregateService->convertMyTopArtistToArrayBySpotifyApi($myTopArtist);
        $genreArray = $this->aggregateService->putArtistGenreArray($myTopArtistArray);

        $this->artistRepository->upsertArtist($myTopArtistArray);
        $this->artistGenreRepository->upsertGenre($genreArray);

        $this->aggregateService->mapArtistGenre($myTopArtistArray);
        $this->aggregateService->decideTopArtist($myTopArtistArray);
    }
}
