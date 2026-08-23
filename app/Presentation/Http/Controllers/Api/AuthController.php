<?php

namespace App\Presentation\Http\Controllers\Api;

use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Requests\LoginRequest;
use App\Presentation\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class AuthController extends Controller
{
    public function login(LoginRequest $request): UserResource
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials, remember: true)) {
            throw ValidationException::withMessages([
                'email' => ['メールアドレスまたはパスワードが正しくありません。'],
            ]);
        }

        $request->session()->regenerate();

        return new UserResource(Auth::user());
    }

    public function logout(Request $request): \Illuminate\Http\Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }

    public function user(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
