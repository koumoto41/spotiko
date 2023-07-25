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

Route::group(['middleware' => 'auth.very_basic'], function () {
    Route::get('/token', 'TokenController@get_token');
    Route::get('/aggregate/artist', 'AggregateController@aggregate_artist');
    Route::get('/aggregate/track', 'AggregateController@aggregate_track');
    Route::get('/', [DisplayController::class, 'getIndex']);

    Route::get('/cms', [TopController::class, 'getIndex']);
    Route::get('/cms/stock', 'Cms\StockController@top');
    Route::get('/cms/stock/form', 'Cms\StockController@form');
    Route::post('/ajax/register/my_album', 'Cms\StockController@register');
});

Route::get('/line', [DisplayController::class, 'lineTest']);
