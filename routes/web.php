<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\Cms\TopController;
use App\Http\Controllers\Cms\StockController;
use App\Http\Controllers\TokenController;

Route::group(['middleware' => 'auth.very_basic'], function () {
    Route::get('/token', [TokenController::class, 'getToken']);
    Route::get('/aggregate/artist', 'AggregateController@aggregate_artist');
    Route::get('/aggregate/track', 'AggregateController@aggregate_track');
    Route::get('/', [DisplayController::class, 'getIndex']);

    Route::get('/cms', [TopController::class, 'getIndex']);
    Route::get('/cms/stock', [StockController::class, 'getIndex']);
    Route::get('/cms/stock/form', [StockController::class, 'getForm']);
    Route::post('/ajax/register/my_album', [StockController::class, 'postRegister']);
});

Route::get('/line', [DisplayController::class, 'lineTest']);
