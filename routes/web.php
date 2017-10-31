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

Route::get('/play', function () {
    return view('test');
})->middleware('auth');

Auth::routes();

Route::get('/home', 'HomeController@index');

Route::group(['prefix' => 'games'], function(){
    Route::get('create', 'GameController@createGame')->name('createGame');
});

Route::get('/','HomeController@index')->name('home');
