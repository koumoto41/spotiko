<?php

namespace App\Library;

require_once '../vendor/autoload.php';

use Illuminate\Http\Request;
use SpotifyWebAPI;

class SpotifyApi {

    private $session;
    private $api;

    /**
     *
     */
    public function set_auth() {

        # オブジェクト作成
        $this->set_session();

        # scopeを設定
        $options = ['scope' => Config('spotify.scope')];

        header('Location: ' . $this->session->getAuthorizeUrl($options));
        die();
    }

    /**
     * アクセストークンを取得する
     * @param $code
     * @param $user_id
     */
    public function get_access_token($code, $user_id) {

        # オブジェクト作成
        $this->set_session();

        $this->session->requestAccessToken($code);
        $this->api->setAccessToken($this->session->getAccessToken());

        # DBに登録する


        # 以下は削除すること
        $option = [
            'limit' => 100,
            'offset' => 0,
            'time_range' => 'short_term'
        ];

        print_r($this->api->getMyTop('artists', $option));

    }

    /**
     * アクセストークンをrefreshする
     * @param $user_id
     * @return mixed
     */
    public function refresh_access_token($user_id) {

        # オブジェクト作成
        $this->set_session();

        # ここでDBからトークン取ってくる
        $refreshToken = '{YOUR_ACCESS_TOKEN}';

        # トークンをリフレッシュして取得する
        $this->session->refreshAccessToken($refreshToken);
        $accessToken = $this->session->getAccessToken();

        # ここでDBに保存する




        return $accessToken;
    }

    /**
     * セッションオブジェクトを作成
     */
    private function set_session() {

        $this->session = new SpotifyWebAPI\Session(
            Config('spotify.CLIENT_ID'),
            Config('spotify.CLIENT_SECRET'),
            Config('spotify.REDIRECT_URI')
        );

        $this->api = new SpotifyWebAPI\SpotifyWebAPI();
    }
}





