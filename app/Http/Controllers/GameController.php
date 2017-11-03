<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\User;
use URL;
use App\Models\Game;
use App\Models\Piece;
use App\Models\Move;
use Auth;


class GameController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

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
        if($query->count()>0) {
            $game = $query->first();

            // user not belong to this game
            if ($game->userBelongsToGame()) {
                $loadPieces = $game->loadPieces();
                list($movesA, $movesB) = $game->getTotalMoves();

                $player = 'other';
                if($game->player_a_id == Auth::user()->id){
                    $player = 'a';
                }
                if($game->player_b_id == Auth::user()->id){
                    $player = 'b';
                }
                return view('games.play', [
                    'config' => $game->setup,
                    'pieces' => json_encode($loadPieces),
                    'player' => $player,
                    'movesA' => $movesA,
                    'movesB' => $movesB,
                ]);
            } else {
                return redirect()->route('home');
            }
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
            $move->player = $movePiece->player;
            $move->piece_id = $piece->id;
            $move->created_at = date("Y-m-d H:i:s");
            $move->position = $json_position;
            $move->save();

            return 'true';
        }

        return 'false';
    }

    public function cyclePost(Request $request)
    {
        $queryMove = Move::where('game_id', '=',$request->gameId)->orderBy('created_at','desc')->limit(1);
        if($queryMove->count()>0){
            $lastMove = $queryMove->get()->toArray()[0];
            $position = json_decode($lastMove['position']);
            $lastMove['position'] = $position;
        }else{
            $lastMove = null;
        }

        $response = [
            "complete" => "true",
            "lastMove" => $lastMove
        ];
        return json_encode($response);

    }

}