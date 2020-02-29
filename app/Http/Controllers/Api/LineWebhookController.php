<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use App\Models;
use App\Library\SpotifyApi;
use App\Library\LineMessageApi;


/**
 * Class LineWebhookController
 * @package App\Http\Controllers\Api
 *
 * @property Models\Top_Artist $top_artist_model
 * @property Models\Top_Track $top_track_model
 *
 */
class LineWebhookController extends Controller
{
    protected $spotify_api_library;
    protected $line_message_api_library;

    const HELP_SEARCH_WORD = 'help';
    const RECOMMEND_SEARCH_WORD = 'おすすめ：';
    const ARTIST_SEARCH_WORD = 'アーティスト';
    const ALBUM_SEARCH_WORD = 'アルバム';
    const TRACK_SEARCH_WORD = '曲';

    public function __construct(
        SpotifyApi $spotify_api_library,
        LineMessageApi $line_message_api_library)
    {
        $this->spotify_api_library = $spotify_api_library;
        $this->line_message_api_library = $line_message_api_library;

        $this->top_artist_model = new Models\Top_Artist();
        $this->top_track_model = new Models\Top_Track();
    }

    public function webhook(Request $request)
    {
        try {
            $signature = $this->line_message_api_library->get_signature($request);
        } catch (Exception $e) {
            \Log::info($e->getMessage());
        }

        $line_bot_object = $this->line_message_api_library->get_line_bot_object();

        try {
            $events = $this->line_message_api_library->get_events($request, $line_bot_object, $signature);

            foreach ($events as $event) {
                $reply_token = $this->line_message_api_library->get_reply_token($event);

                if (!$this->line_message_api_library->exist_exclusion_reply_token($reply_token)) {
                    continue;
                }

                $sent_message = $this->line_message_api_library->get_text($event);
                $reply_message = $this->create_reply_message_content($sent_message);

                if (!$reply_message) {
                    // 返信メッセージが無い場合の処理
                    $reply_message = $sent_message;
                }

                $this->line_message_api_library->set_reply_message($reply_message, $reply_token, $line_bot_object);
            }
        } catch (Exception $e) {
            \Log::info($e->getMessage());
            return;
        }
        return;
    }

    /**
     * 各返信メッセージを作成
     * @param $sent_message
     * @return string
     */
    private function create_reply_message_content($sent_message)
    {
        // おすすめ
        if (mb_strpos($sent_message, self::RECOMMEND_SEARCH_WORD, 0, 'UTF-8') !== false) {
            return $this->create_recommend_content($sent_message);
        }

        // help
        if (strpos($sent_message, self::HELP_SEARCH_WORD) !== false) {
            return $this->create_help_content();
        }

        return '';
    }

    /**
     * おすすめ紹介の実装
     * @param $sent_message
     * @return string
     */
    private function create_recommend_content($sent_message)
    {
        $result = '';
        $search_word = str_replace(self::RECOMMEND_SEARCH_WORD, '', $sent_message);

        if ($search_word == self::ARTIST_SEARCH_WORD) {
            $result = $this->top_artist_model->get_recommend_artist_for_line_bot();
        } elseif ($search_word == self::TRACK_SEARCH_WORD) {
            $result = $this->top_track_model->get_recommend_track_for_line_bot();
        }

        return $result;
    }

    /**
     * 操作説明用
     * @return string
     */
    private function create_help_content()
    {
        $help_text = "説明しよう！\n";
        $help_text .= "おすすめアーティスト紹介は【".self::RECOMMEND_SEARCH_WORD.self::ARTIST_SEARCH_WORD."】！\n";
        $help_text .= "おすすめ曲紹介は【".self::RECOMMEND_SEARCH_WORD.self::TRACK_SEARCH_WORD."】！\n";
        return $help_text;
    }
}