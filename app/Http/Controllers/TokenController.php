<?php

namespace App\Http\Controllers;

require '../vendor/autoload.php';

use Illuminate\Http\Request;
use SpotifyWebAPI;


class TokenController extends Controller
{
    public function get_token() {


        var_dump(Config('spotify.CLIENT_ID'));
        exit;

        $session = new SpotifyWebAPI\Session(
            Config('spotify.CLIENT_ID'),
            Config('spotify.CLIENT_SECRET'),
            Config('spotify.REDIRECT_URI')
        );

        $api = new SpotifyWebAPI\SpotifyWebAPI();

        if (isset($_GET['code'])) {
            $session->requestAccessToken($_GET['code']);
            $api->setAccessToken($session->getAccessToken());

            print_r($api->me());
        } else {
            $options = [
                'scope' => [
                    'user-read-email',
                ],
            ];

            header('Location: ' . $session->getAuthorizeUrl($options));
            die();
        }
    }







}
