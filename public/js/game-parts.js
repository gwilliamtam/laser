
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

    board.first = function(){
        return board[0];
    }

    board.last = function(){
        return board[63];
    }

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

        if(boardIndex.occupiedBy == null){
            var piece = {
                id: id,
                type: type,
                col: col,
                row: row,
                player: player,
                direction: direction,
                image: null
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
    if(save){
        saveMove('r', pieceIndex);
    }
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
console.log("rotationDir", rotationDir, "newDirIndex", newDirIndex, "direction", pieces[pieceIndex].direction, "piece", pieces[pieceIndex]);
    laser.image.rotate(angle);
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
    var origBordIndex = colRowToIndex(piece.col,piece.row)
    var destBordIndex = colRowToIndex(col,row)
    board[origBordIndex].occupiedBy = null;
    board[destBordIndex].occupiedBy = id;
    pieces[piecesIndex[id]].col = col;
    pieces[piecesIndex[id]].row = row;
    var center = board[destBordIndex].center();
    pieces[piecesIndex[id]].image.position = new Point(center.x, center.y);
}

function movePieceOld(index, piece, delta)
{
    var newX=piece.image.position.x+delta.x;
    var newY=piece.image.position.y+delta.y;
    var edges = board.edges();
    if(newX<=edges.left || newX>=edges.right || newY<=edges.top || newY>=edges.bottom){
        var newX=piece.image.position.x;
        var newY=piece.image.position.y;
    }
    var newBoardPosition = findBoardPosition({x:newX, y:newY});
    var pieceBoardPosition = findBoardPosition({x:piece.image.position.x, y:piece.image.position.y})
    var allowMovement = false;
    if(newBoardPosition.index == pieceBoardPosition.index){
        allowMovement = true;
    }else{
        if(board[newBoardPosition.index].occupiedBy == null || board[newBoardPosition.index].occupiedBy == piece.id){
            var available = availableMoveDirections(piece);
            available.forEach(function(avDir){
                if(newBoardPosition.index == colRowToIndex(avDir.col, avDir.row)){
                    allowMovement = true;
                }
            });
        }

    }
    if(allowMovement) {
        pieces[index].image.position = new Point(newX, newY);
        return newBoardPosition.index;
    }else{
        return colRowToIndex(piece.col, piece.row);
    }
}

function saveMove(type, index)
{
    var piece = pieces[index];

    console.log('saving here', type, piece);

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var sendData = {
        "piece": JSON.stringify(piece),
        "type": type
    };
    console.log('sendData',sendData);
    $.post('/games/move', sendData, function(returnData){
        if(returnData != 'true'){
            console.log('piece movement not saved');
        }
    });

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
            if(returnData.complete == 'true'){
                if(playerInTurn != thisPlayer){
                    changePosition(returnData);
                }
                if(returnData.lastMove == null){
                    playerInTurn = nextInTurn(null);
                }else{
                    playerInTurn = nextInTurn(returnData.lastMove.player);
                }
            }
        });
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
    console.log("redrawing player != " + playerInTurn, data);
    retPieces.forEach(function(retPiece){
        if(retPiece.player != thisPlayer ){
            var pieceIndex = piecesIndex[retPiece.id];
            pieces[pieceIndex].col = retPiece.position.col;
            pieces[pieceIndex].row = retPiece.position.row;
            pieces[pieceIndex].direction = retPiece.position.direction;

            var center = board[colRowToIndex(retPiece.position.col, retPiece.position.row)].center();
            if(pieces[pieceIndex].type == "mirror"){
                pieces[pieceIndex].image.remove();
                pieces[pieceIndex].image = createMirror(center.x,center.y, retPiece.player, retPiece.position.direction);
            }
            if(pieces[pieceIndex.type == "laser"]){
                pieces[pieceIndex].image.remove();
                pieces[pieceIndex].image = createLaser(center.x,center.y, retPiece.player, retPiece.position.direction);
            }
        }
    });
    if(data.lastMove!=null){
        // debugger
        var pieceIndex = piecesIndex[data.lastMove.piece_id];
        if(playerInTurn != null && pieces[pieceIndex].player != thisPlayer){
            if(data.lastMove.type == "m"){
                pieces[pieceIndex].col = data.lastMove.position.col;
                pieces[pieceIndex].row = data.lastMove.position.row;
                pieces[pieceIndex].direction = data.lastMove.position.direction;
                var center = board[colRowToIndex(data.lastMove.position.col, data.lastMove.position.row)].center();
                if(pieces[pieceIndex].type == "mirror"){
                    pieces[pieceIndex].image.remove();
                    pieces[pieceIndex].image = createMirror(center.x, center.y, pieces[pieceIndex].player, data.lastMove.position.direction);
                }
                if(pieces[pieceIndex].type == "laser"){
                    pieces[pieceIndex].image.remove();
                    pieces[pieceIndex].image = createLaser(center.x, center.y, pieces[pieceIndex].player, data.lastMove.position.direction);
                }
            }
            if(data.lastMove.type == "f"){
                hideControls(controls)
                laserPaths = fire(data.lastMove.player);
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

function fire(player)
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

        saveMove("f", piecesIndex[laser.id]);
        console.log('laser starts in ',prev);
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
                console.log('laser ends in ',prev);
            }
            prev = next;
        }
    }
    console.log(laserPaths, laserOthers);

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

function showControl(piece, controls)
{
    selectedPieceId = piece.id;
    hideControls(controls);
    var controlsCenter = board[colRowToIndex(piece.col, piece.row)].center();
    if(piece.type == 'mirror'){
        controls.mirror.position = new Point(controlsCenter.x, controlsCenter.y);
        controls.mirror.visible = true;
        controls.mirror.moveAbove(piece.image);
    }
    if(piece.type == 'laser'){
        controls.laser.position = new Point(controlsCenter.x, controlsCenter.y);
        controls.laser.visible = true;
        controls.laser.moveAbove(piece.image);
    }

}

function hideControls(controls)
{
    controls.mirror.visible = false;
    controls.laser.visible = false;
}

function validMovement(id, col, row )
{
    var piece = pieces[piecesIndex[id]];
    var deltaCol = piece.col - col;
    var deltaRow = piece.row - row;
    if(board[colRowToIndex(col,row)].occupiedBy == null){
        if(Math.abs(deltaCol) == 1 && deltaRow == 0){
            return true;
        }
        if(Math.abs(deltaRow) == 1 && deltaCol == 0){
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
    $(".modal-title").html('GAME OVER');
    $(".modal-body").html(playerName(gameOver.winner)+' wins!'+"<br>"+gameOver.reason)
    $("#game-modal").modal("show");
}
