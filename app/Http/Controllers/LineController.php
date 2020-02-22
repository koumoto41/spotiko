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
        // HTTPヘッダを設定
        $channelToken = 'Xzut+uK0Y/jLiKzcH4JXxX8wFqeq2klu+hZK2ZGkakOW2CrIz8jvdHrsXh8nYRHyGt/5amcCmlFGOZpGpdng3cx3EnH+w3uutFpXLOYvCd6TQeG1o8A5AN5uDAM1xhT7JCfJZKNV2VsKPjU0y3fpeAdB04t89/1O/w1cDnyilFU=';
        $headers = [
            'Authorization: Bearer ' . $channelToken,
            'Content-Type: application/json; charset=utf-8',
        ];

// POSTデータを設定してJSONにエンコード
        $post = [
            'to' => 'Ub5e074ef63b4349a01b3a29c817f4025',
            'messages' => [
                [
                    'type' => 'text',
                    'text' => 'hello world',
                ],
            ],
        ];
        $post = json_encode($post);

// HTTPリクエストを設定
        $ch = curl_init('https://api.line.me/v2/bot/message/push');
        $options = [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_POSTFIELDS => $post,
        ];
        curl_setopt_array($ch, $options);

// 実行
        $result = curl_exec($ch);

// エラーチェック
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
