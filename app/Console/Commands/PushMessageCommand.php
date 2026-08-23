<?php

namespace App\Console\Commands;

use App\Domain\Line\LineMessagingClientInterface;
use Illuminate\Console\Command;

class PushMessageCommand extends Command
{
    private const LINE_USER_ID = ['Ub5e074ef63b4349a01b3a29c817f4025'];

    protected $signature = 'command:PushMessage';

    protected $description = 'Line Push送信';

    public function handle(LineMessagingClientInterface $lineMessagingClient): int
    {
        $lineBot = $lineMessagingClient->getBotObject();

        foreach (self::LINE_USER_ID as $lineUserId) {
            $lineMessagingClient->sendPushMessage($lineUserId, 'わかる〜', $lineBot);
        }

        return self::SUCCESS;
    }
}
