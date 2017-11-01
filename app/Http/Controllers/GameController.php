<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\User;
use URL;
use App\Models\Game;
use App\Models\GameSetup;


class GameController extends Controller
{

    protected function redirectTo()
    {
        return route('home');
    }

    //
    public function createGame()
    {
        return view('games.create-game');
    }

    public function validateGame(Request $request)
    {
        $game = New Game;
        if(!$game->exists($request->gameName)){
            return "ok";
        }

        return "Game ".$request->gameName." already exists";
    }

    public function createGamePost(Request $request)
    {
        $game = New Game;
        if($game->create($request->gameName)){
            return "true";
        }

        return "false";
    }

    public function playGame(Request $request)
    {
        // test
        $setup = new GameSetup;
//        dd($setup);

        return view('games.play', [
            'config' => json_encode($setup->config),
            'pieces' => json_encode($setup->pieces)
        ]);
//        return view('games.play-test',['config' => json_encode($setup->config)]);
    }

}