<!DOCTYPE html>
<html>
<head>
    <title>Game</title>
    <link rel="stylesheet" type="text/css" href="/css/game.css">
    <!-- Load the Paper.js library -->
    <script type="text/javascript" src="/js/paper-full.js"></script>
    <!-- Define inlined PaperScript associate it with myCanvas -->

    <script
        src="https://code.jquery.com/jquery-3.2.1.js"
        integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE="
        crossorigin="anonymous"></script>

    <script type="text/javascript" src="/js/game-config.js"></script>
    <script type="text/javascript" src="/js/game-parts.js"></script>

    <script type="text/javascript" canvas="myCanvas">

        // Make the paper scope global, by injecting it into window:
        paper.install(window);
        window.onload = function() {

            // Setup directly from canvas id:
            board = SetBoard();

            var controls = {
                pole: MakePoleControls(),
                laser: MakeLaserControls()
            };
            console.log(controls);

            pieces = new MakePieces();
            pieces.add('laser','1,1','a','s');
            pieces.add('pole','2,1','a', 's');
            pieces.add('pole','3,1','a', 's');
            pieces.add('pole','4,1','a', 's');
            pieces.add('pole','5,1','a', 's');
            pieces.add('pole','6,1','a', 's');
            pieces.add('pole','7,1','a', 's');
            pieces.add('pole','8,1','a', 's');
            pieces.add('pole','9,1','a', 's');
            pieces.add('pole','10,1','a', 's');
            pieces.add('pole','1,2','a','s');
            pieces.add('pole','2,2','a', 's');
            pieces.add('pole','3,2','a', 's');
            pieces.add('pole','4,2','a', 's');
            pieces.add('pole','5,2','a', 's');
            pieces.add('pole','6,2','a', 's');
            pieces.add('pole','7,2','a', 's');
            pieces.add('pole','8,2','a', 's');
            pieces.add('pole','9,2','a', 's');
            pieces.add('pole','10,2','a', 's');

            pieces.add('pole','1,'+config.rowsMax.toString(),'b','n');
            pieces.add('pole','2,'+config.rowsMax.toString(),'b', 'n');
            pieces.add('pole','3,'+config.rowsMax.toString(),'b', 'n');
            pieces.add('pole','4,'+config.rowsMax.toString(),'b', 'n');
            pieces.add('pole','5,'+config.rowsMax.toString(),'b', 'n');
            pieces.add('pole','6,'+config.rowsMax.toString(),'b', 'n');
            pieces.add('pole','7,'+config.rowsMax.toString(),'b', 'n');
            pieces.add('pole','8,'+config.rowsMax.toString(),'b', 'n');
            pieces.add('pole','9,'+config.rowsMax.toString(),'b', 'n');
            pieces.add('laser','10,'+config.rowsMax.toString(),'b', 'n');
            var row = config.rowsMax-1;
            pieces.add('pole','1,'+row.toString(),'b','n');
            pieces.add('pole','2,'+row.toString(),'b', 'n');
            pieces.add('pole','3,'+row.toString(),'b', 'n');
            pieces.add('pole','4,'+row.toString(),'b', 'n');
            pieces.add('pole','5,'+row.toString(),'b', 'n');
            pieces.add('pole','6,'+row.toString(),'b', 'n');
            pieces.add('pole','7,'+row.toString(),'b', 'n');
            pieces.add('pole','8,'+row.toString(),'b', 'n');
            pieces.add('pole','9,'+row.toString(),'b', 'n');
            pieces.add('pole','10,'+row.toString(),'b', 'n');

            pieces.forEach(function(piece){
                paintPiece(piece);
            })

            pieces.forEach(function(piece,index)
            {
                laserPaths = null;

                pieces[index].image.onClick = function(event){
//                    console.log('click on piece');

                    if(draggingPiece){
                        draggingPiece = false;
                    }else{
                        if(piece.type == 'pole'){
                            offLaser(laserPaths);
                            showControl(piece, controls);
                            controls.pole.children[0].onClick = function(event){
                                hideControls(controls);
                            }
                            controls.pole.children[1].onClick = function(event){
                                rotatePole(piece,'l');
                            }
                            controls.pole.children[2].onClick = function(event){
                                rotatePole(piece,'r');
                            }
                        }
                        if(piece.type == 'laser'){
                            offLaser(laserPaths);
                            showControl(piece, controls);
                            controls.laser.children[0].onClick = function(event){
                                hideControls(controls);
                            }
                            controls.laser.children[1].onClick = function(event){
                                rotateLaser(piece, 'l');
                            }
                            controls.laser.children[2].onClick = function(event){
                                rotateLaser(piece, 'r');
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
                        newBoardPosition = movePiece(piece, event.delta);
                    }
                    pieces[index].image.onMouseUp = function(event){

                        offLaser(laserPaths);
                        if(newBoardPosition != null){
                            dropPiece(piece, newBoardPosition);
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
</head>
<body>
<div class="score-board">
    <div class="player-board player-a">
        Player A Moves <span class="moves">0</span>
    </div>
    <div class="player-board player-b">
        Player B Moves <span class="moves">0</span>
    </div>
</div>
<div class="canvas-container">
    <canvas id="myCanvas" ></canvas>
</div>

<style>

</style>
</body>
</html>