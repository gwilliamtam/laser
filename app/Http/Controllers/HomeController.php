<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;
use Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $queryGames = Game::where('player_a_id','=', Auth::user()->id)
            ->orWhere('player_b_id','=', Auth::user()->id);

        if(empty($request->sort)){
            $queryGames->orderby('name');
        }
        else{
            if($request->sort == "date"){
                $queryGames->orderby('created_at', 'asc');
            }
        }


        $usersInGames = array();
        $gameStatus = array();
        if($queryGames->count()>0){

            $games = $queryGames->get();
            $usersInGames = Game::getUsersInGames($games);
            $gameStatus = Game::getGamesStatus($games);

        }else{
            $games = null;
        }

//        $gameSetup = new \App\Models\GameSetup('test',1);
//        $gameSetup->setSize(10);
//        $gameSetup->create();

        $allowDelete = empty($request->d) ? false : true;

        $viewData = [
            "games" => $games,
            "usersInGames" => $usersInGames,
            "gameStatus" => $gameStatus,
            "allowDelete" => $allowDelete
        ];

        if(!empty($request->sort)){
            $viewData['sort'] = $request->sort;
        }

        return view('home', $viewData);
    }

}
