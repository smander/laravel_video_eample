<?php

use Illuminate\Http\Request;

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

//Video
Route::group(['middleware' => 'auth:api','prefix' => 'media'], function () {

    //Get Media Meta Dra
    Route::get('/metadata/{video_id}', 'User\MediaController@get')->middleware('auth:api');

    //Get Total Size
    Route::get('/total/{username}', 'User\MediaController@total')->middleware('auth:api');

    //Save
    Route::post('/save', 'User\MediaController@store')->middleware('auth:api');

    //Update
    Route::patch('/update/{video_id}', 'User\MediaController@update');
});



//Auth Routes
Route::post('register', 'Auth\AuthController@register')->middleware('cors');

Route::post('login', 'Auth\AuthController@login')->middleware('cors');

//Logout Functionality
Route::group(['middleware' => ['cors', 'auth:api']], function () {
    Route::post('logout', 'Auth\AuthController@logout');
});
