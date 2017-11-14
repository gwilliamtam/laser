<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = "users";

    public function isAdmin()
    {
        $admins = explode(',', env('ADMINISTRATORS'));
        if (in_array($this->email, $admins)){
            return true;
        }
        return false;
    }

    public function games()
    {
        return $this->hasMany('App\Models\Game', 'player_a_id', 'id');
    }

    public function otherGames()
    {
        return $this->hasMany('App\Models\Game', 'player_b_id', 'id');
    }
}
