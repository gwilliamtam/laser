<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Game;
use App\Models\Point;
use App\Models\ShotRoute;

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
    private $rutas = array();

    private $dirSymbols = array('n',   'e',   's',    'w',    'ne',   'se',  'sw',    'nw');
    private $dirValues  = array([0,-1], [1,0], [0,1], [-1,0], [1,-1], [1,1], [-1,1], [-1,-1]);

    private $posiblesDirecciones = array(
        'n' => [ 0,-1],
        'e' => [ 1, 0],
        's' => [ 0, 1],
        'w' => [-1, 0]
    );
    private $opposite = array(
        'n' => 's',
        's' => 'n',
        'e' => 'w',
        'w' => 'e',
    );

    public function __construct(Game $game)
    {
        $this->game = $game;
        $this->setup = json_decode($game->setup, true);
        $this->pieces = $this->getPieces();
        $this->board = $this->setBoard();
        $this->arrangePieces();
    }

    /**
     * Decide the robot next move. Will return the piece, type of movement (fire or move), direction and rotations
     *
     * @return array
     */
    public function play()
    {
        $laserPosition = json_decode($this->robotLaser['position'], true);
        $this->generateRoutes(
            0,
            $laserPosition['col'],
            $laserPosition['row']
        );

        dd($this->rutas);

//        $this->possibleRoutes();

//        $this->moveType = $this->moveType();
//
//        if($this->moveType = "m"){
//            $this->movePiece = $this->pieceToMove();
//            $this->moveDirection = $this->moveDirection();
//        }
//
//        if($this->moveType == 'f'){
//            $this->movePiece = $this->laser();
//            $this->moveDirection = $this->fireDirection();
//        }
//
//        // at this point we have defined movement type, piece to move and direction, so we can play
//        // we may return the movement
//        return [
//            'moveType' => $this->moveType,
//            'movePiece' => $this->movePiece,
//            'moveDirection' => $this->moveDirection,
//            'rotations' => $this->rotations
//        ];
    }

    public function generateRoutes($parent = 0, $col, $row, $from = null)
    {
        $node = array(
            'parent' => $parent,
            'from' => $from,
            'col' => $col,
            'row' => $row,
            'dirs' => array()
        );
        array_push($this->rutas, $node);
        $pointer = count($this->rutas)-1;
        if($pointer>100){
            foreach($this->rutas as $key => $ruta){
                echo $key . ' ' . $ruta['from']  . ' ' . $ruta['parent'] . ' ' . $ruta['col'] . ' ' . $ruta['row'] . '<br>';
            }
            die('I just die');
        }
        foreach ($this->posiblesDirecciones as $key => $pair){
            if($from == null or $this->opposite[$from] != $key){
                if($this->validRoute($col, $row, $pair)){
                    $newPos = $this->getNewPosition($col, $row, $pair);
                    $node['dirs'][$key] = $this->generateRoutes($pointer, $newPos['row'], $newPos['col'], $key);
                }

            }
        }
        return $pointer;
    }

    public function validRoute($col, $row, $dirArr)
    {
        $newCol = $col + $dirArr[0];
        $newRow = $row + $dirArr[1];
        if( $newCol >= 1 and $newCol <= intval($this->setup['colsMax']) and
            $newRow >= 1 and $newRow <= intval($this->setup['rowsMax']) )
        {
            return true;
        }
        return false;
    }

    public function getNewPosition($col, $row, $dirArr)
    {
        return [
            'col' => $col + $dirArr[0],
            'row' => $row + $dirArr[1]
        ];

    }

    public function possibleRoutes()
    {
        $laserRoutes = $this->getRoutes($this->robotLaser);
        $laserPosition = json_decode($this->robotLaser['position'], true);

        $routes = array();
        $routes[0] = new RouteNode(0, $laserPosition['col'], $laserPosition['row'], $laserRoutes);

        dd($laserRoutes, $routes);

    }


    public function getRoutes($pieceInBoard = null, $locationInBoard = null)
    {
        $routesJson = null;

        if($pieceInBoard == null){

        }else{
            $dirSymbolsLimit = 0;
            if($pieceInBoard['type'] == 'laser'){
                $dirSymbolsLimit = 3; // first four
            }
            if($pieceInBoard['type'] == 'mirror'){
                $dirSymbolsLimit = 7; // first eight
            }
            $routesArray = array();
            $position = json_decode($pieceInBoard['position'], true);
            for($i=0; $i<=$dirSymbolsLimit; $i++){
                $destinationCol = $position['col'] + $this->dirValues[$i][0];
                $destinationRow = $position['row'] + $this->dirValues[$i][1];
                // first check if past margins
                if( $destinationCol >= 1 and $destinationCol <= intval($this->setup['colsMax']) and
                    $destinationRow >= 1 and $destinationRow <= intval($this->setup['rowsMax']) )
                {
                    array_push($routesArray, $this->dirSymbols[$i]);
                }
            }
            $routesJson = json_encode($routesArray);
        }

        return $routesJson;
    }


    /**
     * Will return an array with all the rotations requested to be done
     *
     * @return array
     */
//    public function setRotations()
//    {
//        return array();
//    }

    /**
     * Decide what type of move do
     * Response can be a "m" of movement or "f" of fire
     *
     * @return char(1)
     */
//    private function moveType()
//    {
//        // to decide if I want to move or fire I need to check first if I have a good shot
//        // so first I will check the route of firing
//        return $type;
//    }

    /**
     * Decide what piece move.
     * Response is one of the elements in $this->pieces
     *
     * @return array
     */
//    private function pieceToMove(){
//        return $piece;
//    }

    /**
     * Decide what direction move the piece selected
     * Response is a movement direction like one of n,s,e,w,ne,nw,se,sw
     * If the piece selected is a laser the direction can be only one of n,s,e,w
     *
     * @return char(1)
     */
//    private function moveDirection(){
//        return $direction;
//    }


    /**
     * Decide what direction fire
     * Can be only one of n,s,e,w. If laser is in an edge the direction must not be in the edge direction.
     *
     * @return char(1)
     */
//    private function fireDirection(){
//        return $direction;
//    }

    /**
     * Get pieces in game and put in array
     *
     * @return array
     */
    private function getPieces()
    {
        $pieces = Piece::where('game_id', '=', $this->game->id)
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
