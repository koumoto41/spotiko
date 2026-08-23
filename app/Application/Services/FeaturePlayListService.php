<?php

namespace App\Application\Services;

use App\Domain\Spotify\SpotifyClientInterface;

final class FeaturePlayListService
{
    public function __construct(private SpotifyClientInterface $spotifyClient) {}

    public function createFeaturePlaylistContent(int $userId = 1): string
    {
        $featurePlaylist = $this->spotifyClient->getFeaturePlaylist($userId, 50);

        $playlistItems = $featurePlaylist->playlists->items;
        $temp = [];

        foreach ($playlistItems as $key => $playlist) {
            $temp[$key]['name'] = $playlist->name;
            $temp[$key]['url'] = $playlist->external_urls->spotify;
        }
        shuffle($temp);

        $result = '';
        foreach ($temp as $key => $value) {
            if ($key === 5) {
                break;
            }
            $result .= "{$value['name']}\n{$value['url']}\n\n";
        }

        return $result;
    }
}
