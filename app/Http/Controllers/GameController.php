<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\User;
use URL;
use App\Models\Game;
use App\Models\Piece;
use App\Models\Move;


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
        if($game->create($request->gameName, $request->userId)){
            return "true";
        }

        return "false";
    }

    public function playGame(Request $request)
    {
        $query = Game::where('name','=', $request->gameName);
        if($query->count()>0){
            $game = $query->first();;

            $loadPieces = $game->loadPieces();
//dd(($game->setup));
            return view('games.play', [
                'config' => $game->setup,
                'pieces' => json_encode($loadPieces)
            ]);

        }

        return view('games.game-not-exist', [
            "gameName" => $request->name
        ]);

    }

    public function movePiecePost(Request $request)
    {
        if(!empty($request->piece)){
            $movePiece = json_decode($request->piece);
            $positon = [
              "col" => $movePiece->col,
              "row" => $movePiece->row,
              "direction" => $movePiece->direction,
            ];
            $json_position = json_encode($positon);

            $piece = Piece::where('id', '=', $movePiece->id)->first();
            $piece->position = $json_position;
            $piece->save();

            $move = new Move;
            $move->game_id = $piece->game_id;
            $move->piece_id = $piece->id;
            $move->created_at = date("Y-m-d H:i:s");
            $move->position = $json_position;
            $move->save();

            return 'true';
        }

        return 'false';
    }

}