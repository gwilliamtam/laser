<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\Models\SetupPiece;
use App\Models\GameSetup;
use App\Models\Move;
use Log;

class Game extends Model
{
    protected $table = "games";
    public $timestamps = false;

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

        $gameSetup = new GameSetup($gameName);
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

}
