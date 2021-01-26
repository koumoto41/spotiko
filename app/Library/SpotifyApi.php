<?php

namespace App\Library;

require_once config('my-app.ROOT_PATH') . '/vendor/autoload.php';

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
class SpotifyApi
{
    const SEARCH_LIMIT = 20;

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

    public function is_refresh_token($exception, $user_id)
    {

        $message = $exception->getMessage();
        $code = $exception->getCode();

        # tokenの期限切れなのでリフレッシュする
        if ($code == 401 && $message == 'The access token expired') {
            $this->refresh_access_token($user_id);
            return true;
        }

        return false;
    }

    /**
     * アクセスtokenを保存する。以前のは削除
     * @param $user_id
     * @param $access_token
     * @param $refresh_token
     */
    private function save_access_token($user_id, $access_token, $refresh_token)
    {
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
        $this->refresh_access_token($user_id);

        $token_data = $this->token_model->get_data_by_user_id($user_id);
        $this->api->setAccessToken($token_data->token);
    }

    /**
     * 自分のTOPチャートを取得
     * @param $user_id
     * @param $type
     * @param int $limit
     * @param int $offset
     * @param string $time_range
     * @return mixed
     */
    public function get_my_top($user_id, $type, $limit = 100, $offset = 0, $time_range = 'short_term')
    {
        $this->set_access_token($user_id);

        $option = [
            'limit' => $limit,
            'offset' => $offset,
            'time_range' => $time_range
        ];

        return $this->api->getMyTop($type, $option);

    }

    public function get_artist_by_id($user_id, $artist_id)
    {
        $this->set_access_token($user_id);
        return $this->api->getArtist($artist_id);

    }

    public function get_album_by_id($user_id, $album_id)
    {
        $this->set_access_token($user_id);
        return $this->api->getAlbum($album_id);
    }

    public function get_audio_features_by_id($user_id, $track_id)
    {
        $this->set_access_token($user_id);
        return $this->api->getAudioFeatures($track_id);
    }

    public function get_new_releases($user_id, $limit = 20, $offset = 0)
    {
        $this->set_access_token($user_id);

        $option = [
            'limit' => $limit,
            'offset' => $offset,
        ];

        return $this->api->getNewReleases($option);
    }

    public function get_feature_playlist($user_id, $limit = 20, $offset = 0)
    {
        $this->set_access_token($user_id);

        $option = [
            'limit' => $limit,
            'offset' => $offset,
        ];

        return $this->api->getFeaturedPlaylists($option);
    }

    /**
     * 検索
     * @param $user_id
     * @param string $query
     * @param string $type album, artist, playlist, track, show, episode
     * @param int $limit
     * @param int $offset
     * @return array|object
     */
    public function search_item($user_id, $query, $type, $limit = self::SEARCH_LIMIT, $offset = 0)
    {
        $this->set_access_token($user_id);

        $params = [
            'limit' => $limit,
            'offset' => $offset
        ];

        return $this->api->search($query, $type, $params);

    }

}





