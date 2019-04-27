<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Library\SpotifyApi;
use Exception;

/**
 * Class TokenController
 * @package App\Http\Controllers
 * @property SpotifyApi $spotify_api_library
 */

class TokenController extends Controller
{

    protected $spotify_api_library;
    const USER_ID = 1;

    public function __construct(SpotifyApi $spotify_api_library)
    {
        $this->spotify_api_library = $spotify_api_library;
    }

    /**
     *
     */
    public function get_token() {
        if (isset($_GET['code'])) {
            # トークンを取得する
            try {
                $this->spotify_api_library->get_access_token($_GET['code'], self::USER_ID);
            } catch (Exception $e) {

                $message = $e->getMessage();
                $code = $e->getCode();

                var_dump($message);
                var_dump($code);
                return;
            }
        } else {
            # トークンを要求する
            $this->spotify_api_library->set_auth();
        }
    }
}
