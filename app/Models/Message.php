<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Message extends Model
{
    protected $table = "messages";
    public $timestamps = false;

    public static function pullMessages($gameId, $last = null)
    {
        $messagesQuery = Self::select('messages.id', 'messages.game_id',
            'messages.for_player_id', 'messages.from_player_id', 'messages.message', 'messages.created_at',
            'users.email', 'users.name')
            ->where('messages.game_id', '=', $gameId)
            ->where('type', '!=', 'can');


        if(empty($last) or $last == null) {
            $messagesQuery->where('messages.created_at', '>=', date("Y-m-d H:i:s", time() - 60 * 60 * 24) );
        } else {
            $messagesQuery->where('messages.created_at', '>', $last);
        }

        $messagesQuery->leftJoin('users', 'users.id', '=', 'messages.from_player_id');
        $messagesQuery->orderBy('messages.created_at', 'asc')
            ->limit(100);

        if($messagesQuery->count()>0){
            $messages = $messagesQuery->get()->toArray();

            $messagesIds = array_column($messages, 'id');
            if(count($messagesIds)>0){
                $updateQuery = 'update laser_messages set type = "can" where type = "temp" and id in ('.implode(",",$messagesIds).')';
                DB::statement($updateQuery);
            }

            return $messages;
        }

        return null;
    }


}
