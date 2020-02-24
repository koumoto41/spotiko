<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\SignatureValidator;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use Exception;


class LineWebhookController extends Controller
{
    public function webhook(Request $request)
    {
        $lineAccessToken = config('my-app.LINE_ACCESS_TOKEN');
        $lineChannelSecret = config('my-app.LINE_CHANNEL_SECRET');

        // 署名のチェック
        $signature = $request->headers->get(HTTPHeader::LINE_SIGNATURE);

        if (!SignatureValidator::validateSignature($request->getContent(), $lineChannelSecret, $signature)) {
            // TODO 不正アクセス
            \Log::info('不正アクセス:'.$signature);
            return;
        }

        $httpClient = new CurlHTTPClient ($lineAccessToken);
        $lineBot = new LINEBot($httpClient, ['channelSecret' => $lineChannelSecret]);

        try {
            // イベント取得
            $events = $lineBot->parseEventRequest($request->getContent(), $signature);

            foreach ($events as $event) {
                $replyToken = $event->getReplyToken();

                if ($replyToken == "00000000000000000000000000000000") {
                    continue;
                }

                $textMessage = new TextMessageBuilder("やんのかこら");
                $lineBot->replyMessage($replyToken, $textMessage);
            }
        } catch (Exception $e) {
            // TODO 例外
            \Log::info($e->getMessage());
            return;
        }

        return;
    }
}