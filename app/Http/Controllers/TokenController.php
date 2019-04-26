<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Library\SpotifyApi;

/**
 * Class TokenController
 * @package App\Http\Controllers
 * @property SpotifyApi $spotify_api_library
 */

class TokenController extends Controller
{

    protected $spotify_api_library;

    public function __construct(SpotifyApi $spotify_api_library)
    {
        $this->spotify_api_library = $spotify_api_library;
    }

    /**
     *
     */
    public function get_token() {

        $user_id = 1;

        // https://example.com/callback?error=access_denied&state=STATE

        if (isset($_GET['code'])) {
            # トークンを取得する
            $this->spotify_api_library->get_access_token($_GET['code'], $user_id);
        } else {
            # トークンを要求する
            $this->spotify_api_library->set_auth();
        }

    }












}
