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
    public $size = null;
    public $shape = null;
    public $opponent = null;
    public function __construct($game, $id)
    {
        $this->shape = 'twoHorizontalLines';
        $this->size = 10;
        $this->name = $game;
        $this->id = $id;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function setShape($shape)
    {
        $this->shape = $shape;
    }

    public function setOpponent($opponent)
    {
        $this->opponent = $opponent;
    }

    public function create()
    {
        $this->config = array (
            'name' => $this->name,
            'id' => $this->id,
            'cycle' => 5,
            'pieceId' => 0,
            'colsMax' => $this->size,
            'rowsMax' => $this->size,
            'sectionWidth' => NULL,
            'sectionHeight' => NULL,
            'shape' => $this->shape,
            'size' => $this->size,
            'opponent' => $this->opponent,
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

        $this->pieces = array();
        if($this->shape == "twoHorizontalLines"){
            $this->pieces = $this->twoHorizontalLines();
        }
        if($this->shape  == "triangleAroundLaser"){
            $this->pieces = $this->triangleAroundLaser();
        }
        if($this->shape  == "spreaded"){
            $this->pieces = $this->spreaded();
        }
    }

    function twoHorizontalLines()
    {
        $piecesSetup = array();

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
        $piecesSetup[] = new SetupPiece(['laser', $this->config['colsMax'], $this->config['rowsMax'], 'b', 'n']);
        return $piecesSetup;
    }

    function triangleAroundLaser()
    {
        $piecesSetup = array();
        $limit = $this->size;
        $totalPieces = $this->size*2;
        $cnt=0;
        for($line=1; $line<=$this->size and $cnt<$totalPieces; $line++){
            $row=$line;
            for($col=1; $col<=$line; $col++){
                if($col==1 and $row==1){
                    $piecesSetup[] = new SetupPiece( ['laser',$col,$row,'a','s']);
                    $piecesSetup[] = new SetupPiece( ['laser',$this->size-$col+1, $this->size-$row+1,'b','n']);
                }else{
                    $piecesSetup[] = new SetupPiece(['mirror', $col, $row, 'a', 'se']);
                    $piecesSetup[] = new SetupPiece(['mirror', $this->size-$col+1, $this->size-$row+1, 'b', 'nw']);
                }
                $cnt++;
                $row--;
            }
        }
        return $piecesSetup;
    }

    function spreaded()
    {
        $piecesSetup = array();
        $limit = $this->size;
        $totalPieces = $this->size*2;
        $cnt=0;
        for($row=1; $row<=$limit and $cnt<$totalPieces; $row++){
            if(($row % 2) != 0){
                $colIni = 1;
            }else{
                $colIni = 2;
            }
            for($col=$colIni; $col<=$limit; $col=$col+2){
                if($col==1 and $row==1){
                    $piecesSetup[] = new SetupPiece( ['laser',$col,$row,'a','s']);
                    $piecesSetup[] = new SetupPiece( ['laser',$this->size-$col+1, $this->size-$row+1,'b','n']);
                }else{
                    $piecesSetup[] = new SetupPiece(['mirror', $col, $row, 'a', 's']);
                    $piecesSetup[] = new SetupPiece(['mirror', $this->size-$col+1, $this->size-$row+1, 'b', 'n']);
                }
                $cnt++;
            }
        }
        return $piecesSetup;
    }

}
