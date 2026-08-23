<?php

namespace App\Presentation\Http\Controllers\Api;

use App\Domain\Spotify\SpotifyClientInterface;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

final class SpotifyAuthController extends Controller
{
    private const USER_ID = 1;

    public function __construct(private SpotifyClientInterface $spotifyClient)
    {
    }

    public function authorizeUrl(): JsonResponse
    {
        return response()->json(['url' => $this->spotifyClient->accessAuthorizeUrl()]);
    }

    public function callback(Request $request): RedirectResponse
    {
        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');
        $code = $request->query('code');

        if (!$code) {
            return redirect()->away("{$frontendUrl}/spotify/callback?status=error");
        }

        try {
            $this->spotifyClient->getAccessToken($code, self::USER_ID);
        } catch (Throwable $e) {
            report($e);
            return redirect()->away("{$frontendUrl}/spotify/callback?status=error");
        }

        return redirect()->away("{$frontendUrl}/spotify/callback?status=success");
    }
}
