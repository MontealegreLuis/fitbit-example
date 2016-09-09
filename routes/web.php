<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/fitbit', 'FitbitAuthenticationController@redirectToProvider');
Route::get('/auth/fitbit/callback', 'FitbitAuthenticationController@handleProviderCallback');
Route::get('/fitbit/activities', 'FitbitController@activities');
