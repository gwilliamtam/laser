<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRobot extends Migration
{
    protected $email = "robot@laserchessgame.com";
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        $user = new \App\Models\User();
        $user->name = "Robot";
        $user->email = $this->email;
        $base = "abcdefghijklmnopqrstuvwxyz";
        $base = $base . strtoupper($base) . "01234567890";
        $pass = "";
        for($i=0;$i<30;$i++){
            $pass = $pass . $base[rand(0,count($base)-1)];
        }
        $user->password = $pass;
        $user->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        $user = \App\Models\User::where('email', '=', $this->email)->first();
        $user->delete();
    }
}
