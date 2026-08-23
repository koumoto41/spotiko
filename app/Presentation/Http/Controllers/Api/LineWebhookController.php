<?php

namespace App\Presentation\Http\Controllers\Api;

use App\Domain\Line\LineMessagingClientInterface;
use App\Domain\Spotify\SpotifyClientInterface;
use App\Domain\TopArtist\Repositories\TopArtistRepositoryInterface;
use App\Domain\TopTrack\Repositories\TopTrackRepositoryInterface;
use App\Presentation\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

final class LineWebhookController extends Controller
{
    private const USER_ID = 1;

    private const HELP_SEARCH_WORD = 'help';

    private const RECOMMEND_SEARCH_WORD = 'おすすめ：';

    private const NEW_RELEASE_SEARCH_WORD = '新着';

    private const FEATURE_PLAYLIST_SEARCH_WORD = '注目プレイリスト';

    private const ARTIST_SEARCH_WORD = 'アーティスト';

    private const TRACK_SEARCH_WORD = '曲';

    public function __construct(
        private LineMessagingClientInterface $lineMessagingClient,
        private SpotifyClientInterface $spotifyClient,
        private TopArtistRepositoryInterface $topArtistRepository,
        private TopTrackRepositoryInterface $topTrackRepository
    ) {}

    public function webhook(Request $request): Response
    {
        $signature = $this->lineMessagingClient->getSignature($request);

        if ($signature === false) {
            Log::info('LINE webhook: invalid signature');

            return response()->noContent();
        }

        $lineBot = $this->lineMessagingClient->getBotObject();

        try {
            $events = $this->lineMessagingClient->getEvents($request, $lineBot, $signature);

            foreach ($events as $event) {
                $replyToken = $this->lineMessagingClient->getReplyToken($event);

                if (! $this->lineMessagingClient->isValidReplyToken($replyToken)) {
                    continue;
                }

                $sentMessage = $this->lineMessagingClient->getText($event);
                $replyMessage = $this->createReplyMessageContent($sentMessage) ?: $sentMessage;

                $this->lineMessagingClient->sendReplyMessage($replyMessage, $replyToken, $lineBot);
            }
        } catch (Exception $e) {
            Log::info($e->getMessage());
        }

        return response()->noContent();
    }

    private function createReplyMessageContent(string $sentMessage): string
    {
        if (mb_strpos($sentMessage, self::RECOMMEND_SEARCH_WORD) !== false) {
            return $this->createRecommendContent($sentMessage);
        }

        if (mb_strpos($sentMessage, self::NEW_RELEASE_SEARCH_WORD) !== false) {
            return $this->createNewReleaseContent();
        }

        if (mb_strpos($sentMessage, self::FEATURE_PLAYLIST_SEARCH_WORD) !== false) {
            return $this->createFeaturePlaylistContent();
        }

        if (strpos($sentMessage, self::HELP_SEARCH_WORD) !== false) {
            return $this->createHelpContent();
        }

        return '';
    }

    private function createRecommendContent(string $sentMessage): string
    {
        $searchWord = str_replace(self::RECOMMEND_SEARCH_WORD, '', $sentMessage);

        if ($searchWord === self::ARTIST_SEARCH_WORD) {
            $targetDate = $this->topArtistRepository->getLatestAggregateDate();
            if (! $targetDate) {
                return '';
            }
            $result = '';
            foreach ($this->topArtistRepository->getTopArtistByAggregateDate($targetDate, true) as $key => $value) {
                if ($key === 5) {
                    break;
                }
                $result .= "{$value->name}\n{$value->url}\n\n";
            }

            return $result;
        }

        if ($searchWord === self::TRACK_SEARCH_WORD) {
            $targetDate = $this->topTrackRepository->getLatestAggregateDate();
            if (! $targetDate) {
                return '';
            }
            $result = '';
            foreach ($this->topTrackRepository->getTopTrackByAggregateDate($targetDate, true) as $key => $value) {
                if ($key === 5) {
                    break;
                }
                $result .= "{$value->track_name} - {$value->album_name}\n{$value->artist_name}\n{$value->track_url}\n\n";
            }

            return $result;
        }

        return '';
    }

    private function createHelpContent(): string
    {
        $helpText = "説明しよう！\n";
        $helpText .= 'おすすめアーティスト紹介は【'.self::RECOMMEND_SEARCH_WORD.self::ARTIST_SEARCH_WORD."】！\n";
        $helpText .= 'おすすめ曲紹介は【'.self::RECOMMEND_SEARCH_WORD.self::TRACK_SEARCH_WORD."】！\n";
        $helpText .= '新着アルバム曲取得は【'.self::NEW_RELEASE_SEARCH_WORD."】！\n";
        $helpText .= '注目のプレイリスト取得は【'.self::FEATURE_PLAYLIST_SEARCH_WORD."】！\n";

        return $helpText;
    }

    private function createNewReleaseContent(): string
    {
        $newReleases = $this->spotifyClient->getNewReleases(self::USER_ID, 50);

        $temp = [];
        foreach ($newReleases->albums->items as $key => $newRelease) {
            $temp[$key]['artist_name'] = current($newRelease->artists)->name;
            $temp[$key]['album_name'] = $newRelease->name;
            $temp[$key]['album_url'] = $newRelease->external_urls->spotify;
        }
        shuffle($temp);

        $result = '';
        foreach ($temp as $key => $value) {
            if ($key === 5) {
                break;
            }
            $result .= "{$value['album_name']}\n{$value['artist_name']}\n{$value['album_url']}\n\n";
        }

        return $result;
    }

    private function createFeaturePlaylistContent(): string
    {
        $featurePlaylist = $this->spotifyClient->getFeaturePlaylist(self::USER_ID, 50);

        $temp = [];
        foreach ($featurePlaylist->playlists->items as $key => $playlist) {
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
