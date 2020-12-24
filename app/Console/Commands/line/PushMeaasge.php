<?php

namespace App\Console\Commands\line;

use Illuminate\Console\Command;
use App\Library\LineMessageApi;

class PushMeaasge extends Command
{
    protected $signature = 'command:PushMessage';
    protected $description = 'Line Push送信';
    protected $line_message_api_library;

    const LINE_USER_ID = ['Ub5e074ef63b4349a01b3a29c817f4025'];

    public function __construct(LineMessageApi $line_message_api_library)
    {
        parent::__construct();
        $this->line_message_api_library = $line_message_api_library;
    }

    public function handle()
    {
        $line_bot_object = $this->line_message_api_library->get_line_bot_object();

        foreach (self::LINE_USER_ID as $line_user_id) {
            $this->line_message_api_library->set_push_message($line_user_id, 'わかる〜', $line_bot_object);






        }
    }
}
