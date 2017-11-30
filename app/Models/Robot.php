<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Game;

class Robot extends Model
{
    private $game = null;
    private $setup = null;
    private $pieces = null;
    private $board = null;
    private $humanPieces = null;
    private $robotPieces = null;
    private $humanLaser = null;
    private $robotLaser = null;
    private $movePiece = null;
    private $moveDirection = null;
    private $moveType = null;

    protected $dirSymbols = array('n',   'e',   's',    'w',    'ne',  'se',  'sw',    'nw');
    protected $dirValues  = array([0,1], [1,0], [0,-1], [-1,0], [1,1], [1,-1], [-1,-1], [-1,1]);

    public function __construct(Game $game)
    {
        $this->game = $game;
        $this->setup = json_decode($game->setup, true);
        $this->pieces = $this->getPieces();
        $this->board = $this->setBoard();
        $this->arrangePieces();
    }

    public function play()
    {
        $this->moveType = $this->moveType();

        if($this->moveType = "m"){
            $this->movePiece = $this->pieceToMove();
            $this->moveDirection = $this->moveDirection();
        }

        if($this->moveType == 'f'){
            $this->movePiece = $this->laser();
            $this->moveDirection = $this->fireDirection();
        }

        // at this point we have defined movement type, piece to move and direction, so we can play
        // we may return the movement
        return [
            'moveType' => $this->moveType,
            'movePiece' => $this->movePiece,
            'moveDirection' => $this->moveDirection
        ];
    }

    /**
     * Decide what type of move do
     * Response can be a "m" of movement or "f" of fire
     *
     * @return char(1)
     */
    private function moveType()
    {
        // to decide if I want to move or fire I need to check first if I have a good shot
        // so first I will check the route of firing
        return $type;
    }

    /**
     * Decide what piece move.
     * Response is one of the elements in $this->pieces
     *
     * @return array
     */
    private function pieceToMove(){
        return $piece;
    }

    /**
     * Decide what direction move the piece selected
     * Response is a movement direction like one of n,s,e,w,ne,nw,se,sw
     * If the piece selected is a laser the direction can be only one of n,s,e,w
     *
     * @return char(1)
     */
    private function moveDirection(){
        return $direction;
    }


    /**
     * Decide what direction fire
     * Can be only one of n,s,e,w. If laser is in an edge the direction must not be in the edge direction.
     *
     * @return char(1)
     */
    private function fireDirection(){
        return $direction;
    }

    /**
     * Get pieces in game and put in array
     *
     * @return array
     */
    private function getPieces()
    {
        $pieces = Piece::where('game_id', '=', $this->id)
            ->get()->toArray();
        return $pieces;
    }

    /**
     * Create a multidimensional array with the same size of the board
     *
     * @return array
     */
    private function setBoard()
    {
        $this->humanPieces = array();
        $this->robotPieces = array();
        $board = array();
        for($col=1; $col<=$this->setup['size']; $col++){
            $board[$col] = array();
            for($row=1; $row<=$this->setup['size']; $row++){
                $board[$col][$row] = null;
            }
        }
        return $board;
    }

    /**
     * Arrange the pieces in the board and store each player pieces in a separate array
     * Also will set the pieces for $this->humanLaser and $this->robotLaser
     */
    private function arrangePieces()
    {
        foreach($this->pieces as $index => $piece){
            $position = json_decode($piece['position']);
            $this->board[$position->col][$position->row] = $piece['id'];
            if($piece['player']=='a'){
                $this->humanPieces[] = [
                    'index' => $index,
                    'id' => $piece['id'],
                    'type' => $piece['type'],
                    'player' => $piece['player'],
                    'col' => $position->col,
                    'row' => $position->row,
                    'dir' => $position->direction
                ];
                if($piece['type'] == 'laser'){
                    $this->humanLaser = $piece;
                }
            }
            if($piece['player']=='b'){
                $this->robotPieces[] = [
                    'index' => $index,
                    'id' => $piece['id'],
                    'type' => $piece['type'],
                    'player' => $piece['player'],
                    'col' => $position->col,
                    'row' => $position->row,
                    'dir' => $position->direction
                ];
                if($piece['type'] == 'laser'){
                    $this->robotLaser = $piece;
                }
            }
        }
    }

    private function evaluateFireRoute($direction)
    {

    }

}
