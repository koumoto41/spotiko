<?php

namespace App\Library;

use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\SignatureValidator;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class LineMessageApi
{
    private $line_access_token;
    private $line_channel_secret;

    public function __construct()
    {
        $this->line_access_token = config('my-app.LINE_ACCESS_TOKEN');
        $this->line_channel_secret = config('my-app.LINE_CHANNEL_SECRET');
    }

    /**
     * 署名をチェックして返す
     * @param $request
     * @return bool
     * @throws LINEBot\Exception\InvalidSignatureException
     */
    public function get_signature($request)
    {
        $signature = $request->headers->get(HTTPHeader::LINE_SIGNATURE);

        if (!SignatureValidator::validateSignature($request->getContent(), $this->line_channel_secret, $signature)) {
            return false;
        }
        return $signature;
    }

    /**
     * line botのオブジェクトを返す
     * @return LINEBot
     */
    public function get_line_bot_object()
    {
        return new LINEBot(new CurlHTTPClient ($this->line_access_token), [
            'channelSecret' => $this->line_channel_secret
        ]);
    }

    /**
     * @param $request
     * @param $line_bot
     * @param $signature
     * @return mixed
     */
    public function get_events($request, $line_bot, $signature)
    {
        return $line_bot->parseEventRequest($request->getContent(), $signature);
    }

    /**
     * @param $event
     * @return mixed
     */
    public function get_reply_token($event)
    {
        return $event->getReplyToken();
    }

    /**
     * @param $reply_message
     * @param $reply_token
     * @param $line_bot
     */
    public function set_reply_message($reply_message, $reply_token, $line_bot)
    {
        $line_bot->replyMessage($reply_token, new TextMessageBuilder($reply_message));
    }
}