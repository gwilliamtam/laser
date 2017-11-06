<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\Models\SetupPiece;
use App\Models\GameSetup;
use App\Models\Move;
use App\Models\Piece;
use Log;
use DB;

class Game extends Model
{
    protected $table = "games";
    public $timestamps = false;

    protected $redirectTo = '/';

    public function exists($gameName){
        $query = $this->where('name', '=', $gameName);
        if($query->count() == 0){
            return false;
        }else{
            return true;
        }
    }

    public function create($gameName, $userId = null){
        $this->name = $gameName;
        $this->player_a_id = $userId;
        $this->player_b_id = null;
        $this->setup = "default";
        $this->save();

        $gameSetup = new GameSetup($gameName, $this->id);
        $this->setup = json_encode($gameSetup->config);

        if($this->save()){
            foreach($gameSetup->pieces as $setupPiece){
                $piece = new Piece();
                $piece->game_id = $this->id;
                $piece->player = $setupPiece->player;
                $piece->type= $setupPiece->type;
                $piece->created_at = date('Y-m-d H:i:s');
                $position = [
                    "col" => $setupPiece->col,
                    "row" => $setupPiece->row,
                    "direction" => $setupPiece->direction
                ];
                $piece->position = json_encode($position);
                $piece->save();

            }
            return true;
        };
        return false;
    }

    public function restart()
    {
        $gameSetup = new GameSetup($this->name, $this->id);
        $this->setup = json_encode($gameSetup->config);

        Piece::where('game_id','=', $this->id)->delete();
        Move::where('game_id','=', $this->id)->delete();
        foreach($gameSetup->pieces as $setupPiece) {
            $piece = new Piece();
            $piece->game_id = $this->id;
            $piece->player = $setupPiece->player;
            $piece->type = $setupPiece->type;
            $piece->created_at = date('Y-m-d H:i:s');
            $position = [
                "col" => $setupPiece->col,
                "row" => $setupPiece->row,
                "direction" => $setupPiece->direction
            ];
            $piece->position = json_encode($position);
            $piece->save();
        }
    }

    public function readyToPlay()
    {
        $ready = true;
        if(empty($this->id) || empty($this->name) ||
            empty($this->player_a_id) || empty($this->player_b_id))
        {
            $ready = false;
        }

        return $ready;
    }

    public function players()
    {
        $queryPlayers = User::whereIn('id',[$this->player_a_id, $this->player_b_id]);
        if($queryPlayers->count()>0){
            return $queryPlayers->all();
        }
        return null;
    }

    public function pieces()
    {
        return $this->hasMany('App\Models\Piece', 'game_id', 'id');
    }

    public function loadPieces()
    {
        $returnPieces = array();
        foreach($this->pieces()->get() as $piece){
            $setupPiece = new SetupPiece($piece);
            $returnPieces[] = $setupPiece;
        };
        return $returnPieces;

    }

    public function getTotalMoves()
    {

        $queryMoves = Move::where('game_id','=',$this->id)
            ->select('player', DB::raw('count(*)'))
            ->groupBy('player')
            ->orderBy('player');

        if($queryMoves->count()==0){
            return [0,0];
        }else{
            $moves = $queryMoves->get()->toArray();
        }
    }

    public function userBelongsToGame()
    {
        $userId = Auth::user()->id;

        if($this->player_a_id == $userId or $this->player_b_id == $userId){
            return true;
        }

        if(empty($this->player_b_id)){
            $this->player_b_id = $userId;
            $this->save();
        }

        return false;
    }

    public function getGameImage($name, $id)
    {
        $queryPieces = Piece::select('pieces.id', 'pieces.player', 'pieces.type', 'pieces.position')
            ->join("games", "games.id", "=", "pieces.game_id")
            ->where("games.name", "=", $name)
            ->where("pieces.game_id", "=", $id);
        $pieces = array();
        if($queryPieces->count()>0){
            $listPieces = $queryPieces->get();
            foreach($listPieces as $piece){
                $piece->position = json_decode($piece->position, true);
                array_push($pieces, $piece);
            }
        }

        $lastMove = null;
        $queryMove = Move::where('game_id', '=',$id)->where('type','=', 'm')->orderBy('created_at','desc')->limit(1);
        if($queryMove->count()>0) {
            $lastMove = $queryMove->get()->toArray();
            $position = json_decode($lastMove[0]['position']);
            $lastMove[0]['position'] = $position;
        }

        $response = [
            "complete" => "true",
            "pieces" => $pieces,
            "lastMove" => $lastMove[0]
        ];

        return json_encode($response);
    }

}
