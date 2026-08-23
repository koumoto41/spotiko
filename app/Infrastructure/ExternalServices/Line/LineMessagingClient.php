<?php

namespace App\Infrastructure\ExternalServices\Line;

use App\Domain\Line\LineMessagingClientInterface;
use Illuminate\Http\Request;
use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\SignatureValidator;

final class LineMessagingClient implements LineMessagingClientInterface
{
    private const EXCLUSION_REPLY_TOKEN_LIST = ['00000000000000000000000000000000'];

    private string $lineAccessToken;

    private string $lineChannelSecret;

    public function __construct()
    {
        $this->lineAccessToken = (string) config('my-app.LINE_ACCESS_TOKEN');
        $this->lineChannelSecret = (string) config('my-app.LINE_CHANNEL_SECRET');
    }

    public function getSignature(Request $request): string|false
    {
        $signature = $request->headers->get(HTTPHeader::LINE_SIGNATURE);

        if (! $signature || ! SignatureValidator::validateSignature($request->getContent(), $this->lineChannelSecret, $signature)) {
            return false;
        }

        return $signature;
    }

    public function getBotObject(): object
    {
        return new LINEBot(new CurlHTTPClient($this->lineAccessToken), [
            'channelSecret' => $this->lineChannelSecret,
        ]);
    }

    public function getEvents(Request $request, object $lineBot, string $signature): array
    {
        return $lineBot->parseEventRequest($request->getContent(), $signature);
    }

    public function getReplyToken(object $event): string
    {
        return $event->getReplyToken();
    }

    public function isValidReplyToken(string $replyToken): bool
    {
        return ! in_array($replyToken, self::EXCLUSION_REPLY_TOKEN_LIST, true);
    }

    public function getText(object $event): string
    {
        return $event->getText();
    }

    public function sendReplyMessage(string $replyMessage, string $replyToken, object $lineBot): void
    {
        $lineBot->replyMessage($replyToken, new TextMessageBuilder($replyMessage));
    }

    public function sendPushMessage(string $lineUserId, string $message, object $lineBot): void
    {
        $lineBot->pushMessage($lineUserId, new TextMessageBuilder($message));
    }
}
