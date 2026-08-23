<?php

namespace App\Domain\Line;

use Illuminate\Http\Request;

interface LineMessagingClientInterface
{
    public function getSignature(Request $request): string|false;

    public function getBotObject(): object;

    public function getEvents(Request $request, object $lineBot, string $signature): array;

    public function getReplyToken(object $event): string;

    public function isValidReplyToken(string $replyToken): bool;

    public function getText(object $event): string;

    public function sendReplyMessage(string $replyMessage, string $replyToken, object $lineBot): void;

    public function sendPushMessage(string $lineUserId, string $message, object $lineBot): void;
}
