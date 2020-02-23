<?php

namespace App\Http\Controllers;

DEFINE('ACCESS_TOKEN', 'Gh/zQUgVN4fO29J4LwXAxiE733+YMRGB/2CGptm57o+jjgevGFHqM8jOuTNireSZGt/5amcCmlFGOZpGpdng3cx3EnH+w3uutFpXLOYvCd5dbasNuEtwpxjl7x6Pk/k4xhy226bfRuYZsbxvtA41BgdB04t89/1O/w1cDnyilFU=');
DEFINE('SECRET_TOKEN', '7d826c1f994ef5adae0d8e3937500dc0');

use Illuminate\Http\Request;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\Constant\HTTPHeader;

require_once config('my-app.ROOT_PATH') . '/vendor/autoload.php';

class LineController extends Controller
{
    public function callback()
    {
        //LINEから送られてきたらtrueになる
        if(isset($_SERVER["HTTP_".HTTPHeader::LINE_SIGNATURE])) {

            //LINEBOTにPOSTで送られてきた生データの取得
            $input_data = file_get_contents("php://input");

            //LINE BOT SDKの設定
            $http_client = new CurlHTTPClient(ACCESS_TOKEN);
            $bot = new LINEBot($http_client, ['channelSecret' => SECRET_TOKEN]);
            $signature = $_SERVER["HTTP_" . HTTPHeader::LINE_SIGNATURE];
            $events = $bot->parseEventRequest($input_data, $signature);

            //大量にメッセージが送られると複数分のデータが同時に送られてくるため、foreachをしている。
            foreach ($events as $event) {
                $send_message = new MultiMessageBuilder();
                $text_message_builder = new TextMessageBuilder("よろぽん！");
                $send_message->add($text_message_builder);
                $bot->replyMessage($event->getReplyToken(), $send_message);
            }
        }
    }
}
