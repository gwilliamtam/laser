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
        $params = [
            'gameName' => $request->gameName,
            'userId' => $request->userId,
            'size' => $request->size,
            'shape' => $request->shape,
            'opponent' => $request->opponent
        ];
        if($game->create($params)){
            return "true";
        }

        return "false";
    }

    public function deletePost(Request $request)
    {
        if(!empty($request->gameName) and !empty($request->gameId) and !empty($request->playerId)){
            $queryGame = Game::Where('id','=',$request->gameId)
                ->where('name','=', $request->gameName)
                ->where('player_a_id','=', $request->playerId);
            if($queryGame->count()>0){
                $queryGame->delete();

                $queryPieces = Piece::where('game_id','=', $request->gameId);
                if($queryPieces->count()>0){
                    $queryPieces->delete();
                }

                $queryMoves = Move::where('game_id', '=', $request->gameId);
                if($queryMoves->count()>0){
                    $queryMoves->delete();
                }
            }
        }
        return redirect()->route('home');
    }

    public function leavePost(Request $request)
    {
        if (!empty($request->gameName) and !empty($request->gameId) and !empty($request->playerId)) {
            $queryGame = Game::Where('id','=',$request->gameId)
                ->where('name','=', $request->gameName)
                ->where('player_b_id','=', $request->playerId);
            $game = $queryGame->first();
            $game->player_b_id = null;
            $game->save();
        }
        return redirect()->route('home');
    }

    public function restartGame(Request $request)
    {
        $gameQuery = Game::where('name','=', $request->name)
            ->where('id', '=', $request->id);
        if($gameQuery->count()>0){
            $game = $gameQuery->first();
            if(Auth::user()->id == $game->player_a_id){
                $game->restart();
            }
            return redirect()->route('playGame', $game->name);
        }
        return redirect()->route('home');

    }

    public function playGame(Request $request)
    {
        $query = Game::where('name','=', $request->gameName);
        if($query->count()>0) {
            $game = $query->first();

            // may be the user not belong to this game
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

                $playerA = $playerB = null;
                $queryPlayerA = User::where('id','=', $game->player_a_id);
                if($queryPlayerA->count()>0){
                    $playerA = $queryPlayerA->first();
                }
                $queryPlayerB = User::where('id','=', $game->player_b_id);
                if($queryPlayerB->count()>0){
                    $playerB = $queryPlayerB->first();
                }

                if(!empty($playerA) and !empty($playerB)){
                    return view('games.play', [
                        'currentGame' => $game,
                        'config' => $game->setup,
                        'pieces' => json_encode($loadPieces),
                        'player' => $player,
                        'players' => [
                            'playerAname' => $playerA->name,
                            'playerBname' => $playerB->name,
                        ],
                        'movesA' => $movesA,
                        'movesB' => $movesB,
                    ]);
                }
            }
            return redirect()->route('home');

        }

        return view('games.game-not-exist', [
            "gameName" => $request->name
        ]);

    }

    public function movePiecePost(Request $request)
    {
        if(!empty($request->piece and !empty($request->type))){

            $game = new Game();
            $game->movePiece($request->piece, $request->type);

            return 'true';
        }

        return 'false';
    }

    public function cyclePost(Request $request)
    {
        $game = new Game();
        $response =  $game->getGameImage($request->gameName, $request->gameId);
        return $response;
    }

    public function getLastShot(Request $request){
        $game = new Game();
        $response =  $game->getLastShot($request->gameName, $request->gameId);
        return $response;
    }


    public function robotPlay(Request $request)
    {
        if(!empty($request->gameId) and !empty($request->gameName)){
            $gameQuery = Game::where('id','=', $request->gameId)
                ->where('name','=', $request->gameName);
            if($gameQuery->count()>0){
                $game = $gameQuery->first();
                $game->robotRandomMovement();
            }
        }
        return 'true';
    }

}