<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse as StreamedResponse;
use App\Models\Game;
use App\Models\Piece;

class CommController extends Controller
{
    public function getBoard(Request $request){

        if(!empty($request->name) and !empty($request->id)){
            $name = $request->name;
            $id = $request->id;
            $response = new StreamedResponse(function() use ($name, $id) {
                $piecesJson = null;
                while (true) {
                    $queryPieces = Piece::select('pieces.id', 'pieces.player', 'pieces.type', 'pieces.position')
                        ->join("games", "games.id", "=", "pieces.game_id")
                        ->where("games.name", "=", $name)
                        ->where("pieces.game_id", "=", $id);
                    if($queryPieces->count()>0){
                        $newPieces = array();
                        $listPieces = $queryPieces->get();
                        foreach($listPieces as $piece){
                            $piece->position = json_decode($piece->position, true);
                            array_push($newPieces, $piece);
                        }
                        $newPiecesJson = json_encode($newPieces);
                        if(strcmp($piecesJson, $newPiecesJson) != 0){
                            echo 'data: ' . $newPiecesJson . "\n\n";
                            ob_flush();
                            flush();
                            sleep(env('APP_STREAM_RESP_TIME'));
                            $piecesJson = $newPiecesJson;
                        }
                    }
                }
            });
            $response->headers->set('Content-Type', 'text/event-stream');
            return $response;
        }


    }
}
