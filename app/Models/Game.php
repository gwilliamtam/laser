<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\Models\SetupPiece;
use App\Models\GameSetup;
use App\Models\Move;
use App\Models\Piece;
use App\Models\User;
use Log;
use DB;

class Game extends Model
{
    protected $table = "games";
    public $timestamps = false;

    protected $moveDirectionsSymbolsLaser = array('n',   'e',   's',    'w',    'ne',  'se',  'sw',    'nw');
    protected $moveDirectionsSymbols = array('n',   'e',   's',    'w',    'ne',  'se',  'sw',    'nw');
    protected $moveDirections =        array([0,1], [1,0], [0,-1], [-1,0], [1,1], [1,-1], [-1,-1], [-1,1]);
    protected $moveDirectionsFront =  [
        [0,1], [1,0], [0,-1], [-1,0], [1,1], [1,-1], [-1,-1], [-1,1],
        [1,0], [1,1], [-1,1], // Add a tendency to move to the north and advance
    ];


    protected $redirectTo = '/';

    public function exists($gameName){
        $query = $this->where('name', '=', $gameName);
        if($query->count() == 0){
            return false;
        }else{
            return true;
        }
    }

    public function create($params){
        $this->name =  $params['gameName'];
        $this->player_a_id = $params['userId'];
        if($params['opponent'] == 'robot'){
            $robot = User::where('email', '=', 'robot@laserchessgame.com')->first();
            $this->player_b_id = $robot->id;
        }else{
            $this->player_b_id = null;
        }

        $this->setup = "default";
        $this->save();

        $gameSetup = new GameSetup($params['gameName'], $this->id);
        $gameSetup->setSize($params['size']);
        $gameSetup->setShape($params['shape']);
        $gameSetup->setOpponent($params['opponent']);
        $gameSetup->create();
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
        }
        return false;
    }

    public function restart()
    {
        $setup = json_decode($this->setup);
        $gameSetup = new GameSetup($this->name, $this->id);
        $gameSetup->setSize($setup->size);
        $gameSetup->setShape($setup->shape);
        $gameSetup->setOpponent($setup->opponent);

        $gameSetup->create();
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

        $this->save();
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

    /**
     * This method will add the player to the game if player B is empty
     *
     * @return bool
     */
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
        $queryMove = Move::where('game_id', '=',$id)
            ->where(function($query){
                $query->where('type','=', 'm')
                    ->orWhere('type','=', 'f')
                    ->orWhere('type','=', 'o');
            })->orderBy('created_at','desc')->limit(1);
        if($queryMove->count()>0) {
            $lastMove = $queryMove->get()->toArray();
            if($lastMove[0]['type'] != "o"){
                $lastMove[0]['position'] = json_decode($lastMove[0]['position']);
            }
        }

        $response = [
            "complete" => "true",
            "pieces" => $pieces,
            "lastMove" => $lastMove[0]
        ];

        return json_encode($response);
    }

    public static function getUsersInGames($games)
    {
        $usersArr = array();
        foreach($games as $game){
            array_push($usersArr, $game->player_a_id);
            array_push($usersArr, $game->player_b_id);
        }
        asort($usersArr);
        $listUserIds = array();
        $prevUserId = null;
        foreach($usersArr as $userId){
            if($userId != null){
                if($prevUserId == null || $prevUserId != $userId){
                    array_push($listUserIds, $userId);
                    $prevUserId = $userId;
                }
            }
        }
        $queryUsers = User::whereIn('id', $listUserIds);
        $userData = array();
        if($queryUsers->count()>0){
            $users = $queryUsers->get();
            foreach($users as $user){
                $userData[$user->id] = [
                    "name" => $user->name,
                    "email" => $user->email
                ];
            }
        }
        return $userData;
    }

    public static function getLastShot($name, $id)
    {
        $shotQuery = Move::where('game_id','=', $id)
            ->where('type', '=', 'f')
            ->orderBy('created_at', 'desc')
            ->limit(1);
        if($shotQuery->count()>0){
            $shot = $shotQuery->get()->toArray();
            if($shot[0]['type'] != "o"){
                $shot[0]['position'] = json_decode($shot[0]['position']);
            }
        }
        $response = [
            "complete" => "true",
            "lastShot" => $shot[0]
        ];
        return json_encode($response);
    }

    public static function getGamesStatus($games)
    {
        $status = array();

        $gamesIds = array_column($games->toArray(),'id');
        $queryEndedGames = Move::select('game_id')
            ->whereIn('game_id',$gamesIds)
            ->where('type', '=', 'o');
        if($queryEndedGames->count()>0){
            $endedGames = $queryEndedGames->get()->toArray();
        }else{
            $endedGames = array();
        }
        $listEndedGames = array_column($endedGames, "game_id");

        foreach($games as $game) {
            $status[$game->id] = null;
            if(!empty($game->player_a_id) && !empty($game->player_b_id)){
                $status[$game->id] = "ready";
            }
            if(empty($game->player_a_id) || empty($game->player_b_id)){
                $status[$game->id] = "wait";
            }
            if(in_array($game->id, $listEndedGames)){
                $status[$game->id] = "over";
            }
        }

        return $status;
    }

    public function movePiece($requestPiece, $requestType, $requestReason = null)
    {
        $movePiece = json_decode($requestPiece);
        $position = [
            "col" => $movePiece->col,
            "row" => $movePiece->row,
            "direction" => $movePiece->direction,
        ];

        $json_position = json_encode($position);

        $piece = Piece::where('id', '=', $movePiece->id)->first();
        $piece->position = $json_position;
        $piece->save();

        $move = new Move;
        $move->type = $requestType;
        $move->game_id = $piece->game_id;
        $move->player = $movePiece->player;
        $move->piece_id = $piece->id;
        $move->created_at = date("Y-m-d H:i:s");
        $move->position = empty($requestReason) ? $json_position : $requestReason;
        $move->save();
    }

    public function createBoard($size, $pieces){
        $playerBPieces = array();
        $board = array();
        for($col=1; $col<=$size; $col++){
            $board[$col] = array();
            for($row=1; $row<=$size; $row++){
                $board[$col][$row] = null;
            }
        }

        foreach($pieces as $index => $piece){
            $position = json_decode($piece['position']);
            $board[$position->col][$position->row] = $piece['id'];
            if($piece['player']=='b'){
                $playerBPieces[] = [
                    'index' => $index,
                    'id' => $piece['id'],
                    'type' => $piece['type'],
                    'player' => $piece['player'],
                    'col' => $position->col,
                    'row' => $position->row,
                    'dir' => $position->direction
                ];
            }
        }

        return [$board,$playerBPieces];
    }

    public function robotRandomMovement(){
        $gameSetup = json_decode($this->setup, true);
        $pieces = Piece::where('game_id', '=', $this->id)
            ->get()->toArray();

        list($board, $playerBpieces) = $this->createBoard($gameSetup['size'], $pieces);

        if(rand(0,100)>90){
            // fire
            $laser = null;
            $laserIndex = null;
            foreach($playerBpieces as $index => $piece){
                if($piece['type'] == "laser"){
                    $laser = $piece;
                    $laserIndex = $index;
                }
            }
            if(!empty($laser)){
                $position = [
                    'col' => $playerBpieces[$laserIndex]['col'],
                    'row' => $playerBpieces[$laserIndex]['row'],
                    'direction' => $this->rotatePieceRandomly('laser')
                ];
                $move = new Move();
                $move->game_id = $this->id;
                $move->piece_id = $playerBpieces[$laserIndex]['id'];
                $move->player = 'b';
                $move->type = 'f';
                $move->created_at = date("Y-m-d H:i:s");
                $move->position = json_encode($position);
                $move->save();
            }
        }else{
            // move
            $foundMove = false;
            $playerBIds = array_column($playerBpieces, 'index');
            $total = count($playerBIds)-1;
            $pieceIndex = rand(0,$total);
            while(!$foundMove){

                list($dCol, $dRow) = $this->moveDirectionsFront[rand(0,count($this->moveDirectionsFront)-1)];
                $newCol = $playerBpieces[$pieceIndex]['col']-$dCol;
                $newRow = $playerBpieces[$pieceIndex]['row']-$dRow;
                if($newCol>=1 and $newCol<=$gameSetup['colsMax'] and $newRow>=1 and $newRow<=$gameSetup['rowsMax']){
                    if($board[$newCol][$newRow] == null){
                        $foundMove = true;
                        $piece = Piece::where('id', '=', $playerBpieces[$pieceIndex]['id'])->first();

                        $newPiece = [
                            'id' => $piece->id,
                            'type' => $piece->type,
                            'player' => $piece->player,
                            'col' => $newCol,
                            'row' => $newRow,
                            'direction' => $this->rotatePieceRandomly($piece->type)
                        ];


                        // some times lets rotate the pieces
                        if(rand(0,100)>33){
                            $this->rotateAllRandomly($playerBpieces);
                        }
                        $this->movePiece(json_encode($newPiece), "m");
                    }
                }
            }
        }


    }

    private function rotateAllRandomly($pieces)
    {
//        var_dump($pieces);
//        dd('die');
        foreach($pieces as $index => $piece){
            // rotate only some of the pieces
            if(rand(0,100)>33){
                $newPiece = [
                    'id' => $piece['id'],
                    'type' => $piece['type'],
                    'player' => $piece['player'],
                    'col' => $piece['col'],
                    'row' => $piece['row'],
                    'direction' => $this->rotatePieceRandomly($piece['type'])
                ];
                $this->movePiece(json_encode($newPiece), "m");
            }
        }
    }

    private function rotatePieceRandomly($type)
    {
        if($type == 'laser'){
            return $this->moveDirectionsSymbolsLaser[rand(0,3)];
        }
        return $this->moveDirectionsSymbols[rand(0,7)];
    }

}
