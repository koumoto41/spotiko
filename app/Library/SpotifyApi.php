<?php

namespace App\Library;

require_once '../vendor/autoload.php';

use Illuminate\Http\Request;
use SpotifyWebAPI;
use App\Models\Token;

/**
 * Class SpotifyApi
 * @package App\Library
 * @property SpotifyWebAPI\SpotifyWebAPI $api
 * @property SpotifyWebAPI\Session $session
 * @property Token $token_model
 */
class SpotifyApi {

    private $session;
    private $api;
    private $token_model;

    public function __construct()
    {
        # オブジェクト作成
        $this->set_session();
        $this->token_model = new Token();
    }


    /**
     * Spotifyに承認コードを要求する
     */
    public function set_auth()
    {
        # scopeを設定
        $options = ['scope' => Config('spotify.scope')];
        header('Location: ' . $this->session->getAuthorizeUrl($options));
        die();
    }

    /**
     * アクセストークンを取得し、DBに登録する
     * @param string $code 承認コード
     * @param $user_id
     */
    public function get_access_token($code, $user_id)
    {
        // 承認コード使ってトークンをリクエスト
        $this->session->requestAccessToken($code);

        // アクセストークンを取得する
        $access_token = $this->session->getAccessToken();
        $refresh_token = $this->session->getRefreshToken();

        // トークン登録
        $this->save_access_token($user_id, $access_token, $refresh_token);

    }

    /**
     * アクセストークンをrefreshして登録する
     * @param $user_id
     * @return mixed
     */
    public function refresh_access_token($user_id)
    {
        # ここでDBからトークン取ってくる
        $token_data = $this->token_model->get_data_by_user_id($user_id);
        $old_access_token = $token_data->refresh_token;

        # トークンをリフレッシュして取得する
        $this->session->refreshAccessToken($old_access_token);

        $access_token = $this->session->getAccessToken();
        $refresh_token = $this->session->getRefreshToken();

        // トークン登録
        $this->save_access_token($user_id, $access_token, $refresh_token);

    }

    /**
     * アクセスtokenを保存する。以前のは削除
     * @param $user_id
     * @param $access_token
     * @param $refresh_token
     */
    private function save_access_token($user_id, $access_token, $refresh_token) {
        # DBに登録する
        if ($this->token_model->get_data_by_user_id($user_id)) {
            # データがあったらdelete
            $this->token_model->delete_token($user_id);
        }
        # tokenを登録
        $this->token_model->set_token($user_id, $access_token, $refresh_token);
    }

    /**
     * セッションオブジェクトを作成
     */
    private function set_session()
    {
        $this->session = new SpotifyWebAPI\Session(
            Config('spotify.CLIENT_ID'),
            Config('spotify.CLIENT_SECRET'),
            Config('spotify.REDIRECT_URI')
        );
        $this->api = new SpotifyWebAPI\SpotifyWebAPI();
    }

    /**
     * トークンをセットする
     * @param $user_id
     */
    private function set_access_token($user_id)
    {
        $token_data = $this->token_model->get_data_by_user_id($user_id);
        $this->api->setAccessToken($token_data->token);
    }

    /**
     * 自分のTOP Artistを取得
     * @param $user_id
     * @return array|object
     */
    public function get_top_artist($user_id)
    {
        $this->set_access_token($user_id);

        $option = [
            'limit' => 100,
            'offset' => 0,
            'time_range' => 'short_term'
        ];

        return $this->api->getMyTop('artists', $option);

    }
}





