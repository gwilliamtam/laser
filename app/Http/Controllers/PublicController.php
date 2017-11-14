<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublicController extends Controller
{

    public function howToPlay(Request $request)
    {
        return view('games.how-to-play');
    }
}
