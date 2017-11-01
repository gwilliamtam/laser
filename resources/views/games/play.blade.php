@extends('layouts.app')

@section('content')
    <div class="container">
        <script type="text/javascript" src="/js/paper-full.js"></script>
        <script>
            var config = JSON.parse('{!! htmlspecialchars_decode($config)  !!}');
            console.log("config", config)
        </script>
        <script type="text/javascript" src="/js/game-config.js"></script>
        <script type="text/javascript" src="/js/game-parts.js"></script>

        <script type="text/javascript" canvas="myCanvas">

            // Make the paper scope global, by injecting it into window:
            paper.install(window);
            window.onload = function() {

                // Setup directly from canvas id:
                board = SetBoard();

                var controls = {
                    mirror: MakemirrorControls(),
                    laser: MakeLaserControls()
                };

                var piecesArr = JSON.parse('{!! $pieces !!}');
                console.log("piecesArr", piecesArr)

                pieces = new MakePieces();
                piecesArr.forEach(function(pieceTmp){
                    pieces.add(pieceTmp.id, pieceTmp.type, pieceTmp.col, pieceTmp.row, pieceTmp.player, pieceTmp.direction);
                });

                pieces.forEach(function(piece,index){
                    paintPiece(index,piece);
                });
                pieces.forEach(function(piece,index)
                {
                    laserPaths = null;
                    pieces[index].image.onClick = function(event){
//                    console.log('click on piece');

                        if(draggingPiece){
                            draggingPiece = false;
                        }else{
                            if(piece.type == 'mirror'){
                                offLaser(laserPaths);
                                showControl(piece, controls);
                                controls.mirror.children[0].onClick = function(event){
                                    hideControls(controls);
                                }
                                controls.mirror.children[1].onClick = function(event){
                                    rotateMirror(index,piece,'l');
                                }
                                controls.mirror.children[2].onClick = function(event){
                                    rotateMirror(index,piece,'r');
                                }
                            }
                            if(piece.type == 'laser'){
                                offLaser(laserPaths);
                                showControl(piece, controls);
                                controls.laser.children[0].onClick = function(event){
                                    hideControls(controls);
                                }
                                controls.laser.children[1].onClick = function(event){
                                    rotateLaser(index,piece, 'l');
                                }
                                controls.laser.children[2].onClick = function(event){
                                    rotateLaser(index,piece, 'r');
                                }
                                controls.laser.children[3].onClick = function(event){
                                    if(draggingPiece){
                                        draggingPiece = false;
                                    }else {
                                        if (piece.type == 'laser') {
                                            hideControls(controls);
                                            laserOn = piece.player;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    pieces[index].image.onMouseDown = function(event){
                        var newBoardPosition = null;
                        pieces[index].image.onMouseDrag = function(event){
                            draggingPiece = true;
//                        console.log('dragging piece');
                            newBoardPosition = movePiece(index, piece, event.delta);
                        }
                        pieces[index].image.onMouseUp = function(event){

                            offLaser(laserPaths);
                            if(newBoardPosition != null){
//                                console.log('about to drop');
                                dropPiece(index, piece, newBoardPosition);
                            }

//                        console.log('piece dropped '+newBoardPosition);
                        }
                    };
                });

                view.onFrame = onFrame;

                function onFrame(event) {
                    var second = parseInt(event.time);
                    var decasecond = parseInt(event.time*10);
//                console.log(second, decasecond, event.time);

                    if(laserOn !== null && laserStop === null){
                        laserPaths = fire(laserOn);
                        laserStop = second + 5;
                    }

                    if(second == laserStop){
                        offLaser(laserPaths);
                        laserStop = null;
                        laserOn = null;
                    }

                    if(laserPaths != null){
                        drawLaser();
                    }
                }

            }


        </script>

        <div class="score-board">
            <div class="player-board player-a">
                Player A Moves <span class="moves">0</span>
            </div>
            <div class="player-board player-b">
                Player B Moves <span class="moves">0</span>
            </div>
        </div>
        <div class="canvas-container text-center">
            <canvas id="myCanvas" ></canvas>
        </div>
@endsection