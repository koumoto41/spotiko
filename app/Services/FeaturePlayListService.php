<?php

namespace App\Services;

use App\Services\SpotifyApiService;

final class FeaturePlayListService
{
    public function __construct(private SpotifyApiService $spotifyApi)
    {
    }

    /**
     * @param int $userId
     * @return string
     */
    public function createFeaturePlaylistContent(int $userId = 1): string
    {
        $this->spotifyApi->refreshAccessToken($userId);
        $featurePlaylist = $this->spotifyApi->getFeaturePlaylist($userId, $limit = 50);

        $playlistItems = $featurePlaylist->playlists->items;
        $result = '';
        $temp = [];

        foreach ($playlistItems as $key => $playlist) {
            $temp[$key]['name'] = $playlist->name;
            $temp[$key]['url'] = $playlist->external_urls->spotify;
        }
        shuffle($temp);

        foreach ($temp as $key => $value) {
            if ($key == 5) {
                break;
            }
            $result .= "{$value['name']}\n{$value['url']}\n\n";
        }
        return $result;
    }
}
