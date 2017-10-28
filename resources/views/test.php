<!DOCTYPE html>
<html>
<head>
    <!-- Load the Paper.js library -->
    <script type="text/javascript" src="/js/paper-full.js"></script>
    <!-- Define inlined PaperScript associate it with myCanvas -->

    <script
        src="https://code.jquery.com/jquery-3.2.1.js"
        integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE="
        crossorigin="anonymous"></script>

    <script type="text/javascript" src="/js/game-parts.js"></script>
    <div class="fire-container">
        <button class="fire">Fire</button>
        <button class="stop">Stop</button>
    </div>

    <script type="text/javascript" canvas="myCanvas">

        var board;
        var pieces;
        var config = {
            pieceId: 0,
            colsMax: 10,
            rowsMax: 10,
            sectionWidth: null,
            sectionHeight: null
        }
        var directions = {
            n: 0,
            ne: 45,
            e: 90,
            se: 135,
            s: 180,
            sw: 225,
            w: 270,
            nw: 315
        };
        var directionsArray = [];
        for(var key in directions){
            directionsArray.push(key);
        }
        var laserPaths = null;
        var laserOn = null;
        var laserStop = null;
        var draggingPiece = false;
        var moves = {
            a: 0,
            b: 0
        }
        var laserDirectionChanges = {
            n: {
                's': 's',
                'se': 'e',
                'sw': 'w'
            },
            s:{
                'n': 'n',
                'ne': 'e',
                'nw': 'w',
            },
            e:{
                'w': 'w',
                'nw': 'n',
                'sw': 's'
            },
            w:{
                'e': 'e',
                'ne': 'n',
                'se': 's'
            }
        };
        // Make the paper scope global, by injecting it into window:
        paper.install(window);
        window.onload = function() {

            // Setup directly from canvas id:
            board = SetBoard();

            var poleControls = MakePoleControls();
            console.log(poleControls);

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
                            showControl(piece, poleControls);
                            poleControls.children[0].onClick = function(event){
                                poleControls.visible = false;
                            }
                            poleControls.children[1].onClick = function(event){
                                rotatePole(piece,'l');
                            }
                            poleControls.children[2].onClick = function(event){
                                rotatePole(piece,'r');
                            }
                        }
                        if(piece.type == 'laser'){
                            poleControls.visible = false;
                            laserOn = piece.player;
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

//            board.forEach(section, index){
//                section.
//            }
console.log(board);
            view.onFrame = onFrame;

            function onFrame(event) {
                var second = parseInt(event.time);

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

            $('.fire').on('click', function(){
                laserPaths = fire('a');
            })
            $('.stop').on('click', function(){
               offLaser(laserPaths);
            })

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
    body{
        margin: 0;
        width: 100vw;
        height: 100vh;
    }

    .score-board{
        width: 100%;
    }
    .player-board{
        width: 50%;
        text-align: center;
    }
    .player-a{
        float: left;
    }
    .player-b{
        float: right;
    }

    .fire-container{
        width: 100%;
        height: 100px;
        text-align: center;
    }
    .fire{
        margin-top: 50px;
    }
    .canvas-container{
        padding: 0;
        margin: 0 auto;
        width: 75%;
        height: 75%;
        min-width: 500px;
        min-height: 500px;
    }
    canvas {
        width: 100%;
        height: 100%;
    }
</style>
</body>
</html>