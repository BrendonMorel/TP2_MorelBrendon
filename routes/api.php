<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/films', 'App\Http\Controllers\FilmController@index');

Route::group(['middleware' => 'throttle:5,1'], function () {
    Route::post('/signup', 'App\Http\Controllers\AuthController@register');
    Route::post('/signin', 'App\Http\Controllers\AuthController@login');
    Route::get('/signout', ['middleware' => 'auth:sanctum', 'uses' => 'App\Http\Controllers\AuthController@logout']);
});

Route::group(['middleware' => 'throttle:60,1', 'auth:sanctum'], function () {
    Route::post('/films', 'App\Http\Controllers\FilmController@store');
    Route::put('/films', 'App\Http\Controllers\FilmController@update');
    Route::delete('/films', 'App\Http\Controllers\FilmController@delete');

    Route::post('/critics', 'App\Http\Controllers\CriticController@store');

    Route::get('/user', 'App\Http\Controllers\UserController@index');
    Route::put('/films', 'App\Http\Controllers\UserController@update');
});
