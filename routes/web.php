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
    Route::post('create', 'GameController@createGamePost')->name('createGamePost');
    Route::get('restart/{name}/{id}', 'GameController@restartGame')->name('restartGame');
    Route::get('validate/{gameName}', 'GameController@validateGame')->name('validateGame');
    Route::post('move', 'GameController@movePiecePost');
    Route::post('cycle', 'GameController@cyclePost');


    // test
    if(env('APP_ENV') == 'local'){
        Route::get('test/create/{gameName}/{user}', 'GameController@createGamePost');
    }
});

Route::get('/play/{gameName}', 'GameController@playGame')->name('playGame');

Route::get('/board/{name}/{id}', 'CommController@getBoard')->name('getBoard');

Route::get('/','HomeController@index')->name('home');

