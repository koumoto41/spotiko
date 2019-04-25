<?php
    namespace App\library;
    use jwilsson\spotify-web-api-php;


    class SpotifyApi {






        public function get_access_token() {

           

            $session = new SpotifyWebAPI\Session('{YOUR_CLIENT_ID}', '{YOUR_CLIENT_SECRET}', '{YOUR_REDIRECt_URI}');


            $api = new SpotifyWebAPI\SpotifyWebAPI();

            if (isset($_GET['code'])) {
                $session->requestAccessToken($_GET['code']);
                $api->setAccessToken($session->getAccessToken());

                print_r($api->me());
                print_r($session->getAccessToken());
            } else {
                header('Location: ' . $session->getAuthorizeUrl(array(
                        'scope' => array(
                            'playlist-read-private', // プレイリスト取得
                            'playlist-modify-private', // プレイリスト変更
                            'user-read-private',
                            'playlist-modify'
                        )
                    )));
                die();





        }







    }






