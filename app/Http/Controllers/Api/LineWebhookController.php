<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use App\Library\SpotifyApi;
use App\Library\LineMessageApi;

class LineWebhookController extends Controller
{
    protected $spotify_api_library;
    protected $line_message_api_library;

    public function __construct(
        SpotifyApi $spotify_api_library,
        LineMessageApi $line_message_api_library)
    {
        $this->spotify_api_library = $spotify_api_library;
        $this->line_message_api_library = $line_message_api_library;
    }

    public function webhook(Request $request)
    {
        try {
            $signature = $this->line_message_api_library->get_signature($request);
        } catch (Exception $e) {
            \Log::info($e->getMessage());
        }

        $line_bot = $this->line_message_api_library->get_line_bot_object();

        try {
            $events = $this->line_message_api_library->get_events($request, $line_bot, $signature);

            foreach ($events as $event) {
                $reply_token = $this->line_message_api_library->get_reply_token($event);

                if ($reply_token == "00000000000000000000000000000000") {
                    continue;
                }

                $reply_message = 'わかる〜〜';
                $this->line_message_api_library->set_reply_message($reply_message, $reply_token, $line_bot);
            }
        } catch (Exception $e) {
            \Log::info($e->getMessage());
            return;
        }
        return;
    }
}