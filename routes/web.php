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
Route::get('/howtoplay', 'PublicController@howToPlay')->name('howToPlay');

Route::group(['prefix' => 'games'], function(){
    Route::get('create', 'GameController@createGame')->name('createGame');
    Route::post('create', 'GameController@createGamePost')->name('createGamePost');
    Route::get('restart/{name}/{id}', 'GameController@restartGame')->name('restartGame');
    Route::get('validate/{gameName}', 'GameController@validateGame')->name('validateGame');
    Route::post('move', 'GameController@movePiecePost');
    Route::post('cycle', 'GameController@cyclePost');
    Route::post('lastShot', 'GameController@getLastShot');
    Route::post('delete', 'GameController@deletePost')->name('deleteGame');
    Route::post('leave', 'GameController@leavePost')->name('leaveGame');
    Route::post('robot', 'GameController@robotPlay');
});

Route::group(['middleware' => 'App\Http\Middleware\AdminMiddleware'], function() {
    Route::get('/admin/users/list', 'AdminController@listUsers')->name('listUsers');
    Route::get('/admin/users/view/{userId}', 'AdminController@viewUser')->name('viewUser');
});

// test
if(env('APP_ENV') == 'local'){
    Route::get('test/create', function(){
        $gameSetup = new \App\Models\GameSetup("test", 1, 'triangleAroundLaser');
        $gameSetup->setSize(15);
        $gameSetup->create();
    });
}

Route::get('/play/{gameName}', 'GameController@playGame')->name('playGame');

Route::get('/board/{name}/{id}', 'CommController@getBoard')->name('getBoard');

Route::get('/','HomeController@index')->name('home');

