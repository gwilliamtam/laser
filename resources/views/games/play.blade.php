@extends('layouts.app')

@section('title',$currentGame->name)

@section('content')
    <div class="container">
        <script type="text/javascript" src="/js/paper-full.js"></script>
        <div class="row">
            <div class="score-board">
                <div class="player-board player-a text-center">
                    <div>
                    {{$players['playerAname']}}
                        {{--<span class="moves">{{$movesA}}</span>--}}
                    </div>
                </div>
                <div class="player-board player-b text-center   ">
                    <div>
                        {{$players['playerBname']}}
                        {{--<span class="moves">{{$movesB}}</span>--}}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="canvas-container text-center">
                <canvas id="myCanvas"></canvas>
            </div>
        </div>

        <div tabindex="0" id="control-popover" data-toggle="popover" data-placement="bottom" data-trigger="focus" title="Action" data-content="Controls" class="text-center"></div>

        <div class="row">
            <div class="player-messages text-center">
                <a class="send-message-link" href="#" data-game-info='{ "id": "{{ $currentGame->id }}", "name": "{{ $currentGame->name }}"}'>
                    <span class="label label-primary"><i class="fa fa-envelope-o" aria-hidden="true"></i></span>
                </a>
            </div>
        </div>

        <div id="movements"></div>

        <div class="board">
        </div>

        @include("games.direct-messages");

        <script>
            var config = JSON.parse('{!! htmlspecialchars_decode($config)  !!}');
            var thisPlayer = "{{$player}}";
            var playerAname = "{{$players['playerAname']}}";
            var playerBname = "{{$players['playerBname']}}";
            var usingRobot = false;
//            console.log(config);
            if(config.opponent && config.opponent == "robot"){
                usingRobot = true;
            }
            var controlsPiece = null;
        </script>
        <script type="text/javascript" src="/js/game-config.js"></script>
        <script type="text/javascript" src="/js/game-parts.js"></script>
        <script type="text/javascript" canvas="myCanvas">

            var controls = null;

            // Make the paper scope global, by injecting it into window:
            paper.install(window);
            window.onload = function() {

                // Setup directly from canvas id:
                board = SetBoard();

                controls = {
                    mirror: MakemirrorControls(),
                    laser: MakeLaserControls()
                };

                var piecesArr = JSON.parse('{!! $pieces !!}');

                pieces = new MakePieces();
                var cnt = 0;
                piecesArr.forEach(function(pieceTmp){
                    pieces.add(pieceTmp.id, pieceTmp.type, pieceTmp.col, pieceTmp.row, pieceTmp.player, pieceTmp.direction);
                    piecesIndex[pieceTmp.id] = cnt;
                    cnt++;
                });

                pieces.forEach(function(piece,index){
                    paintPiece(index,piece);
                });

                view.onClick = function(event){
                    if(gameOver == null){
                        if(playerInTurn == thisPlayer){
                            point = event.point;
                            var clickedPosition = findBoardPosition(point);
                            var pieceId = board[clickedPosition.index].occupiedBy;
                            var piece = null;
                            if(pieceId != null){
                                var index = piecesIndex[pieceId];
                                piece = pieces[index];
                            }
                            if(piece!=null){
                                if(piece.player == thisPlayer){
                                    offLaser(laserPaths);
                                    showControl(piece, controls);
                                }else{
                                    blinkPlayers(thisPlayer);
                                }

                            }else{
                                hideControls();
                                if(playerInTurn == thisPlayer){
                                    if(selectedPieceId!=null){
                                        pieces[piecesIndex[selectedPieceId]].stopStandOut();
                                        if(validMovement(selectedPieceId, clickedPosition.col, clickedPosition.row )){
                                            movePiece(selectedPieceId, clickedPosition.col, clickedPosition.row);
                                            saveMove("m", piecesIndex[selectedPieceId]);
                                            playerInTurn = null;
                                            activePlayer(null);
                                            selectedPieceId = null;
                                        };
                                    }
                                }
                            }
                        }else{
                            blinkPlayers(null);
                        }
                    }
                }

                view.onFrame = onFrame;
                var robotWakeUpAlarms = 0;
                var prevSecond = 0;
                function onFrame(event) {
                    var second = parseInt(event.time);
                    var decasecond = parseInt(event.time*10);

                    if(laserOn !== null && laserStop === null){
                        hideControls()
                        laserPaths = fire(laserOn, true);
                        laserStop = second + 5;
                    }

                    if(gameOver == null && second == laserStop){
                        offLaser(laserPaths);
                        laserStop = null;
                        laserOn = null;
                    }

                    if(laserPaths != null){
                        drawLaser();
                    }

                    if(selectedPieceId != null){
                        pieces[piecesIndex[selectedPieceId]].standOut();
                    }

                    if(laserMove === true){
                        laserMove = null;
                        laserStop = second + 5;
                    }
                    $('.board').html(showBoardPieces());
                    if(second>=cycleExpire){
                        cycleExpire = cycleExpire + config.cycle;
                        cycleTasks();
                    }

                    if(prevSecond != second){
                        prevSecond = second;
                        // plase here anything you need done every second
                    }

                };

            }

        </script>
@endsection