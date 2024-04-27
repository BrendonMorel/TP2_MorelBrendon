<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/films', 'App\Http\Controllers\FilmController@index');

Route::group(['middleware' => 'throttle:5,1'], function () {
    Route::post('/signup', 'App\Http\Controllers\AuthController@register');
    Route::post('/signin', 'App\Http\Controllers\AuthController@login');
    Route::get('/signout', ['middleware' => 'auth:sanctum', 'uses' => 'App\Http\Controllers\AuthController@logout']);
});

Route::group(['middleware' => 'throttle:60,1', 'auth:sanctum'], function () {
    Route::post('/films', 'App\Http\Controllers\FilmController@store')->middleware('role:admin');;
    Route::put('/films/{id}', 'App\Http\Controllers\FilmController@update')->middleware('role:admin');;
    Route::delete('/films/{id}', 'App\Http\Controllers\FilmController@delete')->middleware('role:admin');;

    Route::post('/films/{film_id}/critics', 'App\Http\Controllers\CriticController@store')->middleware('critic_limit');

    Route::get('/users/{id}', 'App\Http\Controllers\UserController@show')->middleware('check_user_ownership');;
    Route::put('/users/{id}/password', 'App\Http\Controllers\UserController@update')->middleware('check_user_ownership')->middleware('confirm_password_match');
});
