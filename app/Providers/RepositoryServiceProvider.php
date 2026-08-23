<?php

namespace App\Providers;

use App\Domain\Album\Repositories\AlbumRepositoryInterface;
use App\Domain\Artist\Repositories\ArtistRepositoryInterface;
use App\Domain\ArtistGenre\Repositories\ArtistGenreRepositoryInterface;
use App\Domain\ArtistGenreMap\Repositories\ArtistGenreMapRepositoryInterface;
use App\Domain\Line\LineMessagingClientInterface;
use App\Domain\MyAlbum\Repositories\MyAlbumRepositoryInterface;
use App\Domain\Spotify\SpotifyClientInterface;
use App\Domain\Token\Repositories\TokenRepositoryInterface;
use App\Domain\TopArtist\Repositories\TopArtistRepositoryInterface;
use App\Domain\TopTrack\Repositories\TopTrackRepositoryInterface;
use App\Domain\Track\Repositories\TrackRepositoryInterface;
use App\Infrastructure\ExternalServices\Line\LineMessagingClient;
use App\Infrastructure\ExternalServices\Spotify\SpotifyApiClient;
use App\Infrastructure\Persistence\Eloquent\Repositories\AlbumRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\ArtistGenreMapRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\ArtistGenreRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\ArtistRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\MyAlbumRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\TokenRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\TopArtistRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\TopTrackRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\TrackRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ArtistRepositoryInterface::class, ArtistRepository::class);
        $this->app->bind(AlbumRepositoryInterface::class, AlbumRepository::class);
        $this->app->bind(TrackRepositoryInterface::class, TrackRepository::class);
        $this->app->bind(MyAlbumRepositoryInterface::class, MyAlbumRepository::class);
        $this->app->bind(ArtistGenreRepositoryInterface::class, ArtistGenreRepository::class);
        $this->app->bind(ArtistGenreMapRepositoryInterface::class, ArtistGenreMapRepository::class);
        $this->app->bind(TopArtistRepositoryInterface::class, TopArtistRepository::class);
        $this->app->bind(TopTrackRepositoryInterface::class, TopTrackRepository::class);
        $this->app->bind(TokenRepositoryInterface::class, TokenRepository::class);

        $this->app->bind(SpotifyClientInterface::class, SpotifyApiClient::class);
        $this->app->bind(LineMessagingClientInterface::class, LineMessagingClient::class);
    }
}
