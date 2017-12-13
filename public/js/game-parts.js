/*jshint multistr: true */
/*jshint esversion: 6 */

function SetBoard()
{
    paper.setup('myCanvas');

    var board = new MakeBoard();
    for(var i=1; i<=config.colsMax; i++){
        boardArray[i] = new Array();
    }
    // boardArray[0][0] values are invalid
    board.forEach(function(section, index){
        boardArray[section.col][section.row] = {
            index: index,
            col: section.col,
            row: section.row,
            xi: section.xi,
            yi: section.yi,
            xf: section.xf,
            yf: section.yf
        };
    });
    board.forEach(function(section){
        var rect = new Rectangle(new Point(section.xi,section.yi), new Point(section.xf,section.yf));
        var boardSection = new Path.Rectangle(rect);
        boardSection.fillColor = section.color;
        boardSection.strokeColor = section.color;

    });

    var boardFrame = new Path.Rectangle({
        from: [board.edges().left, board.edges().top],
        to: [board.edges().right, board.edges().bottom],
        strokeColor: 'black'
    });

    return board;

}

function MakeBoard()
{
    var sizeX = parseInt($('#myCanvas').width() / config.colsMax)-1;
    var sizeY = parseInt($('#myCanvas').height() / config.rowsMax)-1;
    config.sectionWidth = sizeX;
    config.sectionHeight = sizeY;
    sizes.laser.radius = parseInt(sizeX/3);
    sizes.laser.gunRadius = parseInt(sizeX/4);
    sizes.mirror.radius = parseInt(sizeX/3);

    var board = [];
    var xi = 0;
    var yi = 0;
    var colors = [config.board.color1, config.board.color2];
    var cnt = 0;
    for (var rows = 1; rows <= config.rowsMax; rows++) {
        for (var cols = 1; cols <= config.colsMax; cols++) {
            cnt++;
            var xf = xi + sizeX;
            var yf = yi + sizeY;
            var section = MakeSection(cols, rows,xi,yi,xf,yf,colors[cnt % 2])
            board.push(section);
            xi = xi + sizeX + 1;
        }
        if((config.colsMax % 2)==0){
            cnt--;
        }
        yi = yi + sizeY +1;
        xi = 0;
    }


    board.edges = function()
    {
        return {
            top: 0,
            right: xf,
            bottom: yf,
            left: 0,
        }
    }

    return board;
}

function MakeSection(col, row, xi,yi,xf,yf,color)
{
    var section = {
        col: col,
        row: row,
        xi: xi,
        yi: yi,
        xf: xf,
        yf: yf,
        color: color,
        occupiedBy: null
    };

    section.center = function()
    {
        return {
            x: parseInt((section.xi + section.xf) / 2),
            y: parseInt((section.yi + section.yf) / 2)
        }
    }

    return section;
}

function MakePieces()
{
    var pieces = [];

    pieces.add = function(id, type, col, row, player, direction)
    {
        var boardIndex = colRowToIndex(col, row);

        if(boardIndex.occupiedBy == null) {
            var piece = {
                id: id,
                type: type,
                col: col,
                row: row,
                player: player,
                direction: direction,
                image: null,
                standOut: function() {
                    this.image.fillColor.hue += 2;
                },
                stopStandOut: function() {
                    if (this.player == 'a') {
                        this.image.fillColor = config.player.a.color;
                    }
                    if (this.player == 'b') {
                        this.image.fillColor = config.player.b.color;
                    }
                }
            }
            
            board[boardIndex].occupiedBy = piece.id;
            config.pieceId++;
            pieces.push(piece);
        }
    }

    pieces.setImage = function(pieceIndex, image)
    {
        pieces.forEach(function(piece, index){
            if(pieceIndex == index){
                piece.image = image;
            }
        })
    }
    return pieces;
}

function paintPiece(index, piece)
{

    if(piece.type == 'laser'){
        paintLaser(index, piece);
    }
    if(piece.type == 'mirror'){
        var boardIndex = colRowToIndex(piece.col, piece.row);
        var center = board[boardIndex].center();
        var mirrorImage = createMirror(center.x, center.y, piece.player, piece.direction);
        pieces.setImage(index, mirrorImage);
    }
}

function createMirror(x,y, player, direction)
{
    var pieceColor;
    if(player == 'a'){
        pieceColor = config.player.a.color;
    }else{
        pieceColor = config.player.b.color;
    }
    var long = sizes.mirror.radius;
    var mirror = new Path.Arc(
        new Point(x-long, y),
        new Point(x, y+long),
        new Point(x+long, y)
    )
    mirror.fillColor = pieceColor;
    mirror.rotate(directions[direction]);
    mirror.position = new Point(x, y);

    return mirror;
}

function rotateMirror(pieceIndex, piece, rotationDir, save)
{
    if(rotationDir == 'r'){
        var angle = 45;
    }else{
        var angle = -45;
    }

    var currDirIndex = null;
    var newDirIndex = null;
    directionsArray.forEach(function(dir, index){
        if(dir == piece.direction){
            currDirIndex = index;
        }
    });
    if(rotationDir == 'r'){
        newDirIndex = currDirIndex+1;
    }else{
        newDirIndex = currDirIndex-1;
    }
    if(newDirIndex<0){
        newDirIndex = 7;
    }
    if(newDirIndex>7){
        newDirIndex = 0;
    }
    pieces[pieceIndex].direction = directionsArray[newDirIndex];
    piece.image.rotate(angle)
    movementsMessage(piece.player, 'rotated a mirror');
    if(save){
        saveMove('r', pieceIndex);
    }
}

function movementsMessage(player, text){
    // console.log(player, text)
    $('#movements').html(playerName(player)+" "+text);
}

function rotateLaser(pieceIndex, laser, rotationDir, save)
{
    if(rotationDir == 'r'){
        var angle = 90;
    }else{
        var angle = -90;
    }

    var currDirIndex = null;
    var newDirIndex = null;
    directionsArray.forEach(function(dir, index){
        if(dir == laser.direction){
            currDirIndex = index;
        }
    });
    if(rotationDir == 'r'){
        newDirIndex = currDirIndex+2;
    }else{
        newDirIndex = currDirIndex-2;
    }
    if(newDirIndex<0){
        newDirIndex = 6;
    }
    if(newDirIndex>6){
        newDirIndex = 0;
    }
    pieces[pieceIndex].direction = directionsArray[newDirIndex];
    laser.image.rotate(angle);
    movementsMessage(laser.player, 'rotated the laser');
    if(save){
        saveMove('r', pieceIndex);
    }
}

function paintLaser(index, piece)
{
    var boardIndex = colRowToIndex(piece.col, piece.row);
    var center = board[boardIndex].center();
    var laserImage = createLaser(center.x, center.y, piece.player, piece.direction);
    pieces.setImage(index, laserImage);
}

function createLaser(x,y, player, direction)
{
    var pieceColor;
    if(player == 'a'){
        pieceColor = config.player.a.color;
    }else{
        pieceColor = config.player.b.color;
    }
    var centerGun = applyDirection(x,y,direction, parseInt(sizes.laser.radius*.7));

    var path = new CompoundPath({
        children: [
            new Path.Circle({
                center: new Point(x, y),
                radius: sizes.laser.radius
            }),
            new Path.Circle({
                center: new Point(centerGun.x, centerGun.y),
                radius: sizes.laser.gunRadius
            })
        ],
        fillColor: pieceColor
    });
    path.position = new Point({x:x,y:y});

    return path;
}

function applyDirection(x,y,direction, gap)
{
    if(direction=='n'){
        return {x:x, y:y-gap};
    }
    if(direction=='s'){
        return {x:x, y:y+gap};
    }
    if(direction=='e'){
        return {x:x+gap, y:y};
    }
    if(direction=='w'){
        return {x:x-gap, y:y};
    }
}

// converts col row to the index in the board array
function colRowToIndex(col,row)
{
    return (col-1)+((row-1)*(config.colsMax));
}

function indexToColRow(index)
{
    var row = parseInt(index / config.rowsMax);
    var col = index - row;
    return {
        row: row,
        col: col
    }
}

function getIndexByPieceId(id)
{
    var indexFound = null;
    pieces.forEach(function(piece, index){
       if(piece.id == id){
           indexFound = index;
           return false;
       }
    });
    return indexFound;
}

function movePiece(id,col,row)
{
    var piece = pieces[piecesIndex[id]];
    var origBoardIndex = colRowToIndex(piece.col,piece.row)
    var destBoardIndex = colRowToIndex(col,row)
    board[origBoardIndex].occupiedBy = null;
    board[destBoardIndex].occupiedBy = id;
    pieces[piecesIndex[id]].col = col;
    pieces[piecesIndex[id]].row = row;
    var center = board[destBoardIndex].center();
    pieces[piecesIndex[id]].image.position = new Point(center.x, center.y);
}

function saveMove(type, index)
{
    var piece = pieces[index];
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var sendData = {
        "piece": JSON.stringify(piece),
        "type": type
    };
    $.post('/games/move', sendData, function(returnData){
        if(returnData != 'true'){
        }
    });

}

function saveEndOfGame(gameOver)
{

    var laserWinnerIndex = null;
    pieces.forEach(function(aPiece,index){
        if(aPiece.type == "laser" && aPiece.player == gameOver.winner){
            laserWinnerIndex = index;
        }
    })

    var piece = pieces[laserWinnerIndex];
    var type = "o";

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var sendData = {
        "piece": JSON.stringify(piece),
        "type": type,
        "reason": gameOver.reason
    };
    $.post('/games/move', sendData, function(returnData){
        if(returnData != 'true'){
        }
    });
    movementsMessage(null, 'Game Over');
}

function cycleTasks()
{
    if(gameOver == null){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var sendData = {
            gameId: config.id,
            gameName: config.name
        };
        $.post('/games/cycle', sendData, function(returnDataJson){
            var returnData = JSON.parse(returnDataJson);
            // console.log('last move was:', returnData);
            if(returnData.lastMove != null){
                // console.log('last move was from: ', returnData.lastMove.player)
            }
            if(returnData.complete == 'true'){
                    if(playerInTurn != thisPlayer){
                        changePosition(returnData);
                    }
                    if(returnData.lastMove == null){
                        playerInTurn = nextInTurn(null);
                    }else{
                        if(returnData.lastMove.type == "o"){
                            playerInTurn = nextInTurn(null);
                            gameOver = {
                                winner: returnData.lastMove.player,
                                reason: returnData.lastMove.position
                            }
                            fireLastShot();
                            laserOn = true;
                            // endGame();
                        }else{
                            if(returnData.lastMove.type == "m"){
                                movementsMessage(returnData.lastMove.player, "moved a piece "+calcTimeAgo(new Date(returnData.lastMove.created_at+" UTC").getTime(), Date.now()));
                            }
                            if(returnData.lastMove.type == "f"){
                                movementsMessage(returnData.lastMove.player, "fired the laser "+calcTimeAgo(new Date(returnData.lastMove.created_at+" UTC").getTime(), Date.now()));
                            }
                            if(returnData.lastMove.type == "r"){
                                movementsMessage(returnData.lastMove.player, "rotate a piece "+calcTimeAgo(new Date(returnData.lastMove.created_at+" UTC").getTime(), Date.now()));
                            }
                            playerInTurn = nextInTurn(returnData.lastMove.player);
                            console.log("now the turn is for "+playerInTurn);
                            if(playerInTurn == "b"){
                                requestRobotTurn();
                                console.log('requesting robot to play');
                            }
                        }
                    }


            }
        });
    }
}

function calcTimeAgo(timeStampOld, timeStampNew){
    var diff = timeStampNew - timeStampOld;
    var oneMinute = 1000*60;
    var minutes = Math.round(diff/oneMinute);
    if(isNaN(minutes)){
        return "";
    }
    if(minutes==0){
        return " just now";
    }
    if(minutes < 60){
        return minutes + " minutes ago";
    }else{
        var hours = parseInt(minutes / 60);
        var remaingingMin = Math.round( ((minutes / 60) - parseInt(minutes / 60)) * 60 );
        return hours + " hours " + remaingingMin + " minutes ago";
    }
}

function nextInTurn(lastPlayer){
    if(lastPlayer == 'b' || lastPlayer == null){
        var nextPlayer = 'a';

    }else{
        var nextPlayer = 'b';
    }
    activePlayer(nextPlayer);

    return nextPlayer;
}

function fireLastShot()
{
    var sendData = {
        gameId: config.id,
        gameName: config.name
    };
    $.post('/games/lastShot', sendData, function(returnDataJson){
        var returnData = JSON.parse(returnDataJson);
        if(returnData.complete == 'true'){
            if(returnData.lastShot.type == "f"){
                laserOn = true;
                laserPaths = fire(returnData.lastShot.player, false)
            }
        }
    });
}

function activePlayer(player){
    if(player == "a"){
        $('.player-board.player-b').removeClass('next-in-turn');
        $('.player-board.player-a').addClass('next-in-turn');
    }
    if(player == "b"){
        $('.player-board.player-a').removeClass('next-in-turn');
        $('.player-board.player-b').addClass('next-in-turn');
    }
    if(player == null){
        $('.player-board.player-a').removeClass('next-in-turn');
        $('.player-board.player-b').removeClass('next-in-turn');
    }
}

function changePosition(data)
{
    var retPieces = data.pieces;
    retPieces.forEach(function(retPiece){
        if(retPiece.player != thisPlayer ){
            var pieceIndex = piecesIndex[retPiece.id];
            board[colRowToIndex(pieces[pieceIndex].col,pieces[pieceIndex].row)].occupiedBy = null;
            pieces[pieceIndex].col = retPiece.position.col;
            pieces[pieceIndex].row = retPiece.position.row;
            pieces[pieceIndex].direction = retPiece.position.direction;
            var boardIndex = colRowToIndex(retPiece.position.col, retPiece.position.row);
            var center = board[boardIndex].center();
            if(pieces[pieceIndex].type == "mirror"){
                pieces[pieceIndex].image.remove();
                pieces[pieceIndex].image = createMirror(center.x,center.y, retPiece.player, retPiece.position.direction);
            }
            if(pieces[pieceIndex].type == "laser"){
                pieces[pieceIndex].image.remove();
                pieces[pieceIndex].image = createLaser(center.x,center.y, retPiece.player, retPiece.position.direction);
            }
            board[boardIndex].occupiedBy = retPiece.id;
        }
    });
    if(data.lastMove!=null){

        var pieceIndex = piecesIndex[data.lastMove.piece_id];
        if(playerInTurn != null && pieces[pieceIndex].player != thisPlayer){
            if(data.lastMove.type == "f"){
                hideControls()
                laserPaths = fire(data.lastMove.player, false);
                laserMove = true;
            }
        }

    }
}

function dropPiece(index, piece, newBoardPosition, save)
{
    var piecePosition = colRowToIndex(piece.col, piece.row);
    board[piecePosition].occupiedBy = null;
    if(piecePosition != newBoardPosition){
        if(save){
            playerInTurn = null;
        }
        moves[piece.player]++;
        $('.score-board .player-'+piece.player+' .moves').text(moves[piece.player]);
    }
    board[newBoardPosition].occupiedBy = piece.id;
    pieces[index].col = board[newBoardPosition].col;
    pieces[index].row = board[newBoardPosition].row;
    var center = board[newBoardPosition].center();
    pieces[index].image.position = new Point(center.x, center.y);
    movementsMessage(piece.player, 'moved a piece');
    if(save){
        saveMove('m', index);
    }

}

function grabPiece(piece)
{
    copy = piece.image.clone();
    copy.location = new Point(piece.image.position.x, piece.image.position.y);
    copy.fillColor = null;
    copy.strokeColor = piece.image.fillColor;
    return copy;
}

function findBoardPosition(point)
{
    var x = parseInt(point.x);
    var y = parseInt(point.y);
    // board.forEach(function(section,index){
    //
    //     if(point.x>=section.xi && point.x<=section.xf && point.y>=section.yi && point.y<=section.yf){
    //         response = index;
    //     }
    // });
    for(var i=1; i<=config.colsMax; i++){
        if(point.x >= boardArray[i][1].xi && point.x <= boardArray[i][1].xf){
            var colFound = i;
        }
    }
    for(var j=1; j<=config.rowsMax; j++){
        if(point.y >= boardArray[1][j].yi && point.y <= boardArray[1][j].yf){
            var rowFound = j;
        }
    }
    var response = {
        col: colFound,
        row: rowFound,
        index: colRowToIndex(colFound, rowFound)
    }
    return response;
}

function availableMoveDirections(piece)
{
    var availDirections = [];
    availDirections.push({direction: 'self', col: piece.col, row: piece.row});
    if(piece.row>1){
        availDirections.push({direction: 'n', col: piece.col, row: piece.row-1});
    }
    if(piece.row<config.rowsMax){
        availDirections.push({direction: 's', col: piece.col, row: piece.row+1});
    }
    if(piece.col>1){
        availDirections.push({direction: 'e', col: piece.col-1, row: piece.row});
    }
    if(piece.col<config.colsMax){
        availDirections.push({direction: 'w', col: piece.col+1, row: piece.row});
    }
    return availDirections;
}

function piecePositionInBoard(piece)
{
    return colRowToIndex(piece.col, piece.row);
}

function drawLaser()
{
    laserPaths.forEach(function(laserPath){
        laserPath.strokeColor.hue += 1;
    });
    laserOthers.forEach(function(other){
        other.image.fillColor.hue = laserPaths[0].strokeColor.hue;
    });
}


function laserNextColRow(col,row,direction)
{
    var newCol = col;
    var newRow = row;

    if(direction == 'n' && row>1){
        newRow--;
    }
    if(direction == 's' && row<config.rowsMax){
        newRow++;
    }
    if(direction == 'e' && col<config.colsMax){
        newCol++;
    }
    if(direction == 'w' && col>1){
        newCol--;
    }
    if(newCol==col && newRow == row){
        return null;
    }
    return {
        col: newCol,
        row: newRow
    }
}

function fire(player, save)
{
    var laser = null;
    pieces.forEach(function(piece){
       if(piece.player == player && piece.type == 'laser'){
           laser = piece;
       }
    });

    var laserPaths = [];
    if(laser != null){
        var laserDirection = laser.direction;
        var laserSegment = 0
        var calculateTrack = true;
        var cnt = 0;
        var prev = {col: laser.col, row: laser.row};

        if(save){
            saveMove("f", piecesIndex[laser.id]);
        }

        while(calculateTrack && cnt<1000)
        {
            cnt++;
            calculateTrack = false;
            var next = laserNextColRow(prev.col, prev.row, laserDirection);

            if(next != null){
                var boardSection = board[colRowToIndex(next.col, next.row)];
                if(boardSection.occupiedBy == null) {
                    laserPaths.push(calcLaserPath(prev, next));
                    laserSegment++;
                    calculateTrack = true;
                }else{
                    if(pieces[piecesIndex[boardSection.occupiedBy]].type == 'laser'){
                        // end game actions
                        laserPaths.push(calcLaserPath(prev, next));
                        laserSegment++;
                        laserOthers.push(pieces[piecesIndex[boardSection.occupiedBy]]);
                        calculateTrack = false;
                        gameOver = {
                            winner: notPlayer(pieces[piecesIndex[boardSection.occupiedBy]].player),
                            reason: null
                        }
                        if(gameOver.winner == laser.player){
                            gameOver.reason = playerName(gameOver.winner) + " destroyed "+playerName(notPlayer(gameOver.winner))
                        }else{
                            gameOver.reason = playerName(notPlayer(gameOver.winner)) + " Auto Destroyed! Seems like " + playerName(notPlayer(gameOver.winner)) + " found it was too much!";
                        }

                        if(save){
                            saveEndOfGame(gameOver);
                        }
                        endGame();
                    }else{
                        if(pieces[piecesIndex[boardSection.occupiedBy]].type == 'mirror'){
                            laserDirection = changeLaserDirection(laserDirection, pieces[piecesIndex[boardSection.occupiedBy]].direction);
                            laserOthers.push(pieces[piecesIndex[boardSection.occupiedBy]]);
                        }
                        if(laserDirection != null){
                            laserPaths.push(calcLaserPath(prev, next));
                            laserSegment++;
                            calculateTrack = true;
                        }else{
                            laserPaths.push(calcLaserPath(prev, next));
                            laserSegment++;
                            calculateTrack = false;
                        }
                    }
                }

            }
            if(next == null){
            }
            prev = next;
        }
    }
    return laserPaths;

}

function calcLaserPath(prev, next)
{
    var segmentStart = board[colRowToIndex(prev.col, prev.row)].center();
    var segmentEnd = board[colRowToIndex(next.col, next.row)].center();
    var laserPath = new Path.Line({
        from: new Point(segmentStart),
        to: new Point(segmentEnd),
        strokeColor: config.laser.color,
        strokeWidth: config.laser.width
    });
    return laserPath;
}

function changeLaserDirection(laserDirection, mirrorDirection)
{
    var group = laserDirectionChanges[laserDirection];
    if(group.hasOwnProperty(mirrorDirection)){
        return group[mirrorDirection];
    }else
    {
        return null;
    }
}

function offLaser(listPaths)
{
    if(listPaths!=null){
        listPaths.forEach(function(path){
            path.remove();
        })
    }
    laserOthers.forEach(function(piece){
        piece.image.fillColor = config.player[piece.player].color;
    });
    laserOthers = [];

}

function MakemirrorControls()
{
    project.activeLayer = 2;
    var rect = new Rectangle(new Point(1,1), new Point(config.sectionWidth-1,config.sectionHeight-1));
    var bg = new Path.Rectangle(rect);
    bg.fillColor = 'white';
    bg.stokeColor = 'black';
    bg.fillColor.alpha = 0.5;

    center = {
        x: parseInt((config.sectionWidth)/2)+1,
        y: parseInt((config.sectionHeight)/2)+1
    }

    // var left = new Raster('/img/rotate_left.png');
    // left.scale(0.05);

    var left = new Path.RegularPolygon(new Point(center.x-15,center.y), 3, 10);
    left.rotate(-90);
    left.fillColor = 'black';
    left.position =new Point(center.x-15, center.y-12);

    var right = new Path.RegularPolygon(new Point(center.x+15, center.y), 3, 10);
    right.rotate(90);
    right.fillColor = 'black';
    right.position = new Point(center.x+15, center.y-12);

    right.moveAbove(bg);

    var controlBoard = new Group([bg, left, right]);
    controlBoard.visible = false;

    return controlBoard;
}

function MakeLaserControls()
{
    project.activeLayer = 2;
    var rect = new Rectangle(new Point(1,1), new Point(config.sectionWidth-1,config.sectionHeight-1));
    var bg = new Path.Rectangle(rect);
    bg.fillColor = 'white';
    bg.stokeColor = 'black';
    bg.fillColor.alpha = 0.5;

    center = {
        x: parseInt((config.sectionWidth)/2)+1,
        y: parseInt((config.sectionHeight)/2)+1
    }

    // var left = new Raster('/img/rotate_left.png');
    // left.scale(0.05);

    var left = new Path.RegularPolygon(new Point(center.x-15,center.y), 3, 10);
    left.rotate(-90);
    left.fillColor = 'black';
    left.position =new Point(center.x-15, center.y-12);

    var right = new Path.RegularPolygon(new Point(center.x+15, center.y), 3, 10);
    right.rotate(90);
    right.fillColor = 'black';
    right.position = new Point(center.x+15, center.y-12);

    var fireButton = new Path.Rectangle(new Point(center.x+7, center.y+7), new Point(center.x-7, center.y-7));
    fireButton.fillColor = 'black';
    fireButton.position = new Point(center.x, center.y+12);

    right.moveAbove(bg);

    var controlBoard = new Group([bg, left, right, fireButton]);
    controlBoard.visible = false;

    return controlBoard;
}

function paintControlContent(piece)
{
    controlsPiece = piece;
    var content = `
    <div class="button-container">
    <button class="btn btn-primary" id="controls-rotate-left"><i class="fa fa-undo" aria-hidden="true"></i></button>
    <button class="btn btn-primary" id="controls-rotate-right"><i class="fa fa-repeat" aria-hidden="true"></i></button>
    <br>
    `;
    if(piece.type == "laser"){
        content = content + '<button id="controls-button-fire" class="btn btn-danger text-center">Fire <i class="fa fa-fire" aria-hidden="true"></i></button><br>'
    }
    content = content + '<button id="controls-button-move" class="btn btn-primary text-center">Move <i class="fa fa-arrows" aria-hidden="true"></i></button><br>';
    content = content + '</div>';

    return content;
}



function showControl(piece, controls)
{
    if(selectedPieceId != null){
        pieces[piecesIndex[selectedPieceId]].stopStandOut();
    }
    selectedPieceId = piece.id;
    // hideControls();
    var controlsCenter = board[colRowToIndex(piece.col, piece.row)].center();

    var popPlacer = $('#control-popover');
    var canvasPosition = $('#myCanvas').position();
    var canvasWidth = $('#myCanvas').width();
    var popOverWidth = 150;
    popPlacer.popover('show');
    var popOver = $('#control-popover').next();
    popOver.find('.popover-title').html('Select Action <button id="controls-button-close"><span aria-hidden="true">&times;</span></button>');
    popOver.find('.popover-content').html(paintControlContent(piece));
    var popLeft =  canvasPosition.left + controlsCenter.x - (popOverWidth / 2);
    // to far to the left
    if(popLeft < canvasPosition.left){
        console.log('far to the left');
        popLeft =  canvasPosition.left + 5;
        var popArrowLeft = controlsCenter.x - 5;
    }
    // to far to the right
    if(popLeft + popOverWidth > canvasPosition.left + canvasWidth){
        console.log('far to the right', popLeft + popOverWidth , canvasPosition.left + canvasWidth);
        popLeft = canvasPosition.left + canvasWidth - popOverWidth - 15;
        var popArrowLeft = popOverWidth - (canvasWidth - controlsCenter.x) + 15;
    }
    // var popTop = canvasPosition.top + controlsCenter.y + (config.sectionHeight / 2) - 5;
    var popTop = canvasPosition.top + controlsCenter.y;
    console.log('width', popOverWidth, 'left', popLeft,  'top', popTop, 'arrowLeft', popArrowLeft);
    popOver.css({width: popOverWidth, left: popLeft,  top: popTop});
    if(popArrowLeft){
        popOver.find('.arrow').css('left', popArrowLeft)
    }

    popOver.find('#controls-button-close').on('click', function(){
        pieces[piecesIndex[selectedPieceId]].stopStandOut();
        selectedPieceId = null;
        hideControls();
    });
    popOver.find('#controls-button-move').on('click', function(){
        // $(document).find('#control-popover').popover('hide');
        hideControls();
    });

    console.log(controlsPiece);
    if(piece.type == "laser"){
        popOver.find('#controls-rotate-left').on('click', function(){
            rotateLaser(piecesIndex[piece.id], piece, 'l', true);
        });
        popOver.find('#controls-rotate-right').on('click', function(){
            rotateLaser(piecesIndex[piece.id], piece, 'r', true);
        });
        popOver.find('#controls-button-fire').on('click', function(){
            if (draggingPiece) {
                draggingPiece = false;
            } else {
                if (piece.type == 'laser') {
                    // $(document).find('#control-popover').popover('hide');
                    hideControls();
                    laserOn = piece.player;
                }
            }
        });
    }
    if(piece.type == "mirror"){
        popOver.find('#controls-rotate-left').on('click', function(){
            rotateMirror(piecesIndex[piece.id], piece, 'l', true);
        });
        popOver.find('#controls-rotate-right').on('click', function(){
            rotateMirror(piecesIndex[piece.id], piece, 'r', true);
        });
    }





    // if(piece.type == 'mirror'){
    //     controls.mirror.position = new Point(controlsCenter.x, controlsCenter.y);
    //     controls.mirror.visible = true;
    //     controls.mirror.moveAbove(piece.image);
    // }
    // if(piece.type == 'laser'){
    //     controls.laser.position = new Point(controlsCenter.x, controlsCenter.y);
    //     controls.laser.visible = true;
    //     controls.laser.moveAbove(piece.image);
    // }

}

// function hideControls(controls)
function hideControls()
{
    // controls.mirror.visible = false;
    // controls.laser.visible = false;
    $(document).find('#control-popover').popover('hide');
}

function validMovement(id, col, row )
{
    var piece = pieces[piecesIndex[id]];
    var deltaCol = piece.col - col;
    var deltaRow = piece.row - row;
    if(board[colRowToIndex(col,row)].occupiedBy == null){
        // console.log(deltaCol, deltaRow);
        if(Math.abs(deltaCol) == 1 && Math.abs(deltaRow) == 0){
            return true;
        }
        if(Math.abs(deltaCol) == 0 && Math.abs(deltaRow) == 1){
            return true;
        }
        if(Math.abs(deltaCol) == 1 && Math.abs(deltaRow) == 1){
            return true;
        }
    }
    return false;
}

function blinkPlayers(player)
{
    if(player == null){
        $('.player-board').fadeOut(150).fadeIn(150).fadeOut(150).fadeIn(150);
    }else{
        $('.player-'+player).fadeOut(150).fadeIn(150).fadeOut(150).fadeIn(150);
    }

}

function playerName(player)
{
    if(player == 'a'){
        return playerAname;
    }
    if(player == 'b'){
        return playerBname;
    }
    return null;
}

function notPlayer(player)
{
    if(player == "a"){
        return "b";
    }
    if(player == "b"){
        return "a";
    }
    return null;
}

function endGame()
{
    activePlayer(null);
    $(".player-"+gameOver.winner).html(playerName(gameOver.winner)+" wins!");
    // $(".modal-title").html('GAME OVER');
    // $(".modal-body").html("<strong>"+playerName(gameOver.winner)+' wins!</strong>'+"<br>"+gameOver.reason)
    // $("#game-modal").modal("show");

    // console.log(config);
    $('.modal-title').html(config.gameName);
    $('#messages-modal').modal("show");
    $('#messages-modal').attr("game-info", '{"id":"'+config.id+'", "name": "'+config.name+'"}');
    $('#messages-zone').append("Game Over. "+"<strong>"+playerName(gameOver.winner)+' wins!</strong>'+"<br>"+gameOver.reason+"<br>");
}

function showBoardPieces()
{
    var boardRow = "";
    var boardText = "";

    for(var j=1; j<=config.rowsMax; j++){
        for(var i=1; i<=config.colsMax; i++) {
            var boardIndex = boardArray[i][j].index;
            var pieceId = board[boardIndex].occupiedBy;
            if(pieceId == null){
                var piece = null;
            }else{
                var piece = pieces[piecesIndex[pieceId]];
            }
            if(pieceId == null){
                var segment = "[_]";
            }else{
                var segment = "["+piece.type.substr(0,1)+"]";
            }
            boardRow = boardRow + segment;
        }
        boardText = boardText + boardRow + " " + j + "<br>";
        boardRow = "";
    }
    return boardText;
}

function requestRobotTurn()
{
        console.log('requestRobotTurn', usingRobot)
    if(usingRobot){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var sendData = {
            player: "b",
            gameId: config.id,
            gameName: config.name
        };
        $.post('/games/robot', sendData, function(complete){
            // if(complete == 'true'){
            //     console.log('request complete');
            // }
        });
    }
}
