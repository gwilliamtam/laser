<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\Models\SetupPiece;

class Game extends Model
{
    protected $table = "games";

    public function exists($gameName){
        $query = $this->where('name', '=', $gameName);
        if($query->count() == 0){
            return false;
        }else{
            return true;
        }
    }

    public function create($gameName){
        $this->name = $gameName;
        $this->player_a_id = Auth::user()->id;
        if($this->save()){
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

//    public function startGame()
//    {
//        if($this->readyToPlay())
//        $this->initialSetup();
//    }


}
