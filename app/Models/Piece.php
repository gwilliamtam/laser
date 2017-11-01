<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Piece extends Model
{
    protected $table = "pieces";
    public $timestamps = false;

    public function lastMove()
    {
        return Move::orderBy('created_at', 'desc')->first();
    }

    public function moves()
    {
        return $this->hasMany('App\Models\Move', 'piece_id', 'id');
    }

}
