<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetSpotifyTokenRequest;
use App\UseCases\GetSpotifyAccessToken;
use App\UseCases\GetSpotifyAuthorizationCode;
use Exception;

final class SpotifyTokenController extends Controller
{
    public function getAccessToken(GetSpotifyTokenRequest $request, GetSpotifyAccessToken $getSpotifyToken): void
    {
        $params = $request->validate($request->rules());
        try {
            $getSpotifyToken(data_get($params, 'code'), data_get($params, 'user_id'));
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function requestAuthorizationCode(GetSpotifyAuthorizationCode $getSpotifyAuthorizationCode): void
    {
        $getSpotifyAuthorizationCode();
    }
}
