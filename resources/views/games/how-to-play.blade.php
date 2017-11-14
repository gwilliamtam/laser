@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">How To Play</div>

                <div class="panel-body">

                    <dl class="how-to">
                        <p>Welcome to Willy Laser Chess. This is a personal project that I want to share with you. All your comments about this game are welcome. Have fun!</p>
                        <dt>Register and Login</dt>
                        <dd>You need to register and login to play. Your email is your username and can be registered only once.</dd>

                        <dt>Create a game</dt>
                        <dd>The first step is to create a game. Select <strong>Create</strong> from <stron>Games</stron> menu and type the name of your game. Your game name is also the URL you will use to return to your game. For example, is your game name is: JohnVsRichard your will access your name at the URL {!! url('/play/JohnVsRichard') !!}. Your game name must be unique. This means we will <strong>Check</strong> if the game name exist before let you create a game.</dd>

                        <dt>Start game</dt>
                        <dd>After creating your game you will see your game URL in your screen. You need to copy this URL and send it to the other player using any messaging system in your phone or your computer. You can not enter your game until the other player as click on the game URL. The game creator will be the blue player at the top of the board. The guest invited will be the red player at the bottom of the board.</dd>

                        <dt>Game Options</dt>
                        <dd><strong>Size:</strong> The number of columns times the number rows. If your game size is 8x8 you will have 8 cols and 8 rows.</dd>
                        <dt></dt>
                        <dd><strong>Shape:</strong> Your game shape will define how the pieces are distributed in your board. Currently I show three different shapes:<br>
                            <br>
                            <div class="game-shape text-center">
                                <button type="button" class="btn btn-default" data-shape="twoHorizontalLines">
                                    <img src="/img/twoHorizontalLines.png" class="game-icon"><br>
                                    <span>Two Horizontal Lines</span>
                                </button>
                                <button type="button" class="btn btn-default" data-shape="triangleAroundLaser">
                                    <img src="/img/triangleAroundLaser.png" class="game-icon"><br>
                                    <span>Triangle Around Laser</span>
                                </button>
                                <button type="button" class="btn btn-default" data-shape="spreaded">
                                    <img src="/img/spreaded.png" class="game-icon"><br>
                                    <span>Spreaded</span>
                                </button>
                            </div>
                        </dd>

                        <dt>Pieces</dt>
                        <dd><strong>Number of Pieces:</strong> The number of pieces is defined by your game size. The game will try to fit as many pieces as the twice the size of a column. For example, if your game size is 10x10 then you will have 20 pieces approximately.</dd>
                        <dd><strong>Laser:</strong> You will have only one laser. Laser can move only one position in any direction (vertical, horizontal or diagonal). A laser movement finish your turn. A laser fire finish your turn. </dd>
                        <dd><strong>Mirror:</strong> A mirror can move one position in any direction (vertical, horizontal or diagonal). The flat area of a mirror will reflect your laser or the other player laser. The round area of the laser can absorb any laser without any damage. You can rotate any laser as many times you want without losing your turn. Moving a mirror will finish your turn.</dd>

                        <dt>Objective</dt>
                        <dd>Hit the other player laser before you are hit. One hit and game over. Be careful, do not hit yourself.</dd>

                        <dt>Duration</dt>
                        <dd>This is chess. There is no sense in adding duration to a game. You can play without interruption or return later to play your turn. You only need to login with your account to access all the games you are playing.</dd>

                        <dt>Delete Games</dt>
                        <dd>You can only delete the games you crated. If you are in a game you have not created you can leave the game. Use the <strong>Delete On/Off</strong> button to delete or leave games.</dd>

                        <dt>Number of games</dt>
                        <dd>Each account can have only three games.</dd>

                        <dt>Contact and Troubleshooting</dt>
                        <dd>I am working in a forum, a contact form or any other type of messaging system and will be available soon.</dd>
                    </dl>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
