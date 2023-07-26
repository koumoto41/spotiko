<?php

namespace App\Http\Controllers;

use App\Services\SpotifyApiService;
use Exception;


class TokenController extends Controller
{
    private const USER_ID = 1;

    public function __construct(private SpotifyApiService $spotifyApiService)
    {
    }

    public function getToken()
    {
        $code = request('code');

        if ($code) {
            // トークンを取得する
            try {
                $this->spotifyApiService->getAccessToken(
                    $code,
                    self::USER_ID
                );
            } catch (Exception $e) {
                $message = $e->getMessage();
                $code = $e->getCode();

                var_dump($message);
                var_dump($code);
                return;
            }
        } else {
            // トークンを要求する
            $this->spotifyApiService->accessAuthorizeUrl();
        }
    }
}
