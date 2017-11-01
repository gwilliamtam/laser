<?php

namespace App\Models;

class SetupPiece
{
    public $id = null;
    public $type = null;
    public $col = null;
    public $row = null;
    public $player = null;
    public $direction = null;

    public function __construct($piece)
    {
        if(is_array($piece)){
            $this->id = null;
            $this->type = $piece[0];
            $this->player = $piece[3];
            $this->col = $piece[1];
            $this->row = $piece[2];
            $this->direction = $piece[4];
        }
        if(is_object($piece)){
            $position = json_decode($piece->position);

            $this->id = $piece->id;
            $this->type = $piece->type;
            $this->player = $piece->player;
            $this->col = $position->col;
            $this->row = $position->row;
            $this->direction = $position->direction;
        }
    }
}
