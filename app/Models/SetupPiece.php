<?php

namespace App\Models;

class SetupPiece
{
    public $type = null;
    public $col = null;
    public $row = null;
    public $player = null;
    public $direction = null;

    public function __construct($type, $col, $row, $player, $direction)
    {
        $this->type = $type;
        $this->col = $col;
        $this->row = $row;
        $this->player = $player;
        $this->direction = $direction;
    }
}
