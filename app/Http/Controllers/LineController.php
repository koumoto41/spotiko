<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LineController extends Controller
{
    public function callback_test()
    {
        http_response_code(200);
    }

    public function callback()
    {
        // まずは HTTPステータス 200 を返す
        http_response_code(200) ;
        echo '200 {}';

// 送られて来たJSONデータを取得
        $json_string = file_get_contents('php://input');
        $json = json_decode($json_string);
// JSONデータから返信先を取得
        $replyToken = $json->events[0]->replyToken;
// JSONデータから送られてきたメッセージを取得
        $message = $json->events[0]->message->text;

// HTTPヘッダを設定
        $channelToken = 'Gh/zQUgVN4fO29J4LwXAxiE733+YMRGB/2CGptm57o+jjgevGFHqM8jOuTNireSZGt/5amcCmlFGOZpGpdng3cx3EnH+w3uutFpXLOYvCd5dbasNuEtwpxjl7x6Pk/k4xhy226bfRuYZsbxvtA41BgdB04t89/1O/w1cDnyilFU=';
        $headers = [
            'Authorization: Bearer ' . $channelToken,
            'Content-Type: application/json; charset=utf-8',
        ];

// POSTデータを設定してJSONにエンコード
        $post = [
            'replyToken' => $replyToken,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => '「' . $message . '」',
                ],
            ],
        ];
        $post = json_encode($post);

// HTTPリクエストを設定
        $ch = curl_init('https://api.line.me/v2/bot/message/reply');
        $options = [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_POSTFIELDS => $post,
        ];

// 実行
        curl_setopt_array($ch, $options);

// エラーチェック
        $result = curl_exec($ch);
        $errno = curl_errno($ch);
        if ($errno) {
            return;
        }

// HTTPステータスを取得
        $info = curl_getinfo($ch);
        $httpStatus = $info['http_code'];

        $responseHeaderSize = $info['header_size'];
        $body = substr($result, $responseHeaderSize);

// 200 だったら OK
        echo $httpStatus . ' ' . $body;
    }
}
