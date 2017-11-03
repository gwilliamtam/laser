<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMovesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('moves', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id');
            $table->string('piece_id', 10);
            $table->char('player', 1);
            $table->timestamp('created_at');
            $table->longText('position'); // json with {col: int, row: int, direction: char}
            $table->index(['game_id','piece_id']);
            $table->index(['piece_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('moves');

    }
}
