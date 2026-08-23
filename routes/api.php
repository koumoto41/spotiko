<?php

use App\Presentation\Http\Controllers\Api\AggregationController;
use App\Presentation\Http\Controllers\Api\AuthController;
use App\Presentation\Http\Controllers\Api\LineWebhookController;
use App\Presentation\Http\Controllers\Api\RankingController;
use App\Presentation\Http\Controllers\Api\SpotifyAuthController;
use App\Presentation\Http\Controllers\Api\StockController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

Route::post('/login', [AuthController::class, 'login']);

// LINEプラットフォームからのサーバー間通信。署名検証のみで認証するためSanctumセッションの対象外。
Route::post('/webhook', [LineWebhookController::class, 'webhook'])->name('line.webhook');

// SpotifyのOAuthリダイレクト。ブラウザ経由でユーザーのセッション外から叩かれるため対象外。
Route::get('/spotify/callback', [SpotifyAuthController::class, 'callback']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/rankings', RankingController::class);

    Route::get('/stock', [StockController::class, 'index']);
    Route::get('/stock/search', [StockController::class, 'search']);
    Route::post('/stock', [StockController::class, 'store']);

    Route::get('/spotify/authorize-url', [SpotifyAuthController::class, 'authorizeUrl']);

    Route::post('/aggregate/artist', [AggregationController::class, 'artist']);
    Route::post('/aggregate/track', [AggregationController::class, 'track']);
});
