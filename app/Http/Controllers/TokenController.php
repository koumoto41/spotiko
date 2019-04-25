<?php

namespace App\Http\Controllers;

require '../vendor/autoload.php';

use Illuminate\Http\Request;
use SpotifyWebAPI;


class TokenController extends Controller
{
    public function get_token() {

        $session = new SpotifyWebAPI\Session(
            Config('spotify.CLIENT_ID'),
            Config('spotify.CLIENT_SECRET'),
            Config('spotify.REDIRECT_URI')
        );

        $api = new SpotifyWebAPI\SpotifyWebAPI();

        if (isset($_GET['code'])) {
            $session->requestAccessToken($_GET['code']);
            $api->setAccessToken($session->getAccessToken());

            $option = [
                'limit' => 100,
                'offset' => 0,
                'time_range' => 'short_term'
            ];

            print_r($api->getMyTop('artists', $option));
            
        } else {
            $options = [
                'scope' => [
                    'user-top-read',
                ],
            ];

            header('Location: ' . $session->getAuthorizeUrl($options));
            die();
        }
    }







}
