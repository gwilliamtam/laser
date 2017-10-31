<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\User;
use URL;

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

}