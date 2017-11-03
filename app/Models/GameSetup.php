<?php

namespace App\Models;

use App\Models\Piece;
use App\Models\Game;

class GameSetup
{
    public $config = null;
    public $pieces = null;
    public $id = null;
    public $game = null;

    public function __construct($game, $id)
    {
        $this->name = $game;
        $this->id = $id;
        $this->initialSetup();
    }

    private function initialSetup()
    {
        $this->config = array (
            'name' => $this->name,
            'id' => $this->id,
            'cycle' => 5,
            'pieceId' => 0,
            'colsMax' => 10,
            'rowsMax' => 10,
            'sectionWidth' => NULL,
            'sectionHeight' => NULL,
            'board' =>
                array (
                    'color1' => '#d0d0d0',
                    'color2' => '#f0f0f0',
                ),
            'player' =>
                array (
                    'a' =>
                        array (
                            'color' => 'blue',
                        ),
                    'b' =>
                        array (
                            'color' => 'red',
                        ),
                ),
            'laser' =>
                array (
                    'color' => 'yellow',
                    'width' => 3,
                ),
        );

        $piecesSetup = array();
        // player a

        $piecesSetup[] = new SetupPiece( ['laser',1,1,'a','s']);
        for($col=2; $col<=$this->config['colsMax']; $col++){
            $piecesSetup[] = new SetupPiece(['mirror', $col,1, 'a', 's']);
        }
        for($col=1; $col<=$this->config['colsMax']; $col++){
            $piecesSetup[] = new SetupPiece(['mirror', $col, 2, 'a', 's']);
        }
        // player b
        for($col=1; $col<=$this->config['colsMax']; $col++){
            $piecesSetup[] = new SetupPiece(['mirror', $col, $this->config['rowsMax']-1, 'b', 'n']);
        };
        for($col=1; $col<=$this->config['colsMax']-1; $col++){
            $piecesSetup[] = new SetupPiece(['mirror', $col, $this->config['rowsMax'], 'b', 'n']);
        };
        $piecesSetup[] = new SetupPiece(['laser', 10, $this->config['rowsMax'], 'b', 'n']);
        $this->pieces = $piecesSetup;

    }

}
