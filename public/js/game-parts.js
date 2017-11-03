
function SetBoard()
{
    paper.setup('myCanvas');

    var board = new MakeBoard();

    board.forEach(function(section, index){
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
        var pieceColor;
        if(piece.player == 'a'){
            pieceColor = config.player.a.color;
        }else{
            pieceColor = config.player.b.color;
        }

        var long = 15;
        var mirror = new Path.Arc(
            new Point(center.x-long, center.y),
            new Point(center.x, center.y+long),
            new Point(center.x+long, center.y)
        )
        mirror.fillColor = pieceColor;
        mirror.rotate(directions[piece.direction]);
        mirror.position = new Point(center.x, center.y);
        pieces.setImage(index, mirror);
    }
}

function rotateMirror(pieceIndex, piece, rotationDir)
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

    piece.image.rotate(angle);
    saveMove(pieceIndex);
}

function rotateLaser(pieceIndex, laser, rotationDir)
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
    saveMove(pieceIndex);
}

function paintLaser(index, piece)
{
    var boardIndex = colRowToIndex(piece.col, piece.row);
    var center = board[boardIndex].center();
    var pieceColor;
    if(piece.player == 'a'){
        pieceColor = config.player.a.color;
    }else{
        pieceColor = config.player.b.color;
    }
    var centerGun = applyDirection(center.x,center.y,piece.direction);

    var path = new CompoundPath({
        children: [
            new Path.Circle({
                center: new Point(center.x, center.y),
                radius: 15
            }),
            new Path.Circle({
                center: new Point(centerGun.x, centerGun.y),
                radius: 8
            })
        ],
        fillColor: pieceColor
    });
    path.position = new Point(center);
    pieces.setImage(index, path);
}

function applyDirection(x,y,direction)
{
    var gap = 15;
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

function movePiece(index, piece, delta)
{
    var newX=piece.image.position.x+delta.x;
    var newY=piece.image.position.y+delta.y;
    var edges = board.edges();
    if(newX<=edges.left || newX>=edges.right || newY<=edges.top || newY>=edges.bottom){
        var newX=piece.image.position.x;
        var newY=piece.image.position.y;
    }
    var newBoardPosition = findBoardPosition(newX, newY);
    var allowMovement = false;
    if(newBoardPosition == findBoardPosition(piece.image.position.x, piece.image.position.y)){
        allowMovement = true;
    }else{
        if(board[newBoardPosition].occupiedBy == null || board[newBoardPosition].occupiedBy == piece.id){
            var available = availableMoveDirections(piece);
            available.forEach(function(avDir){
                if(newBoardPosition == colRowToIndex(avDir.col, avDir.row)){
                    allowMovement = true;
                }
            });
        }

    }
    if(allowMovement) {
        pieces[index].image.position = new Point(newX, newY);
        return newBoardPosition;
    }else{
        return colRowToIndex(piece.col, piece.row);
    }
}

function saveMove(index)
{
    var piece = pieces[index];

    console.log('saving here', piece);

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var sendData = {
        piece: JSON.stringify(piece)
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
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var sendData = {
        gameId: config.id,
        gameName: config.name
    };
    console.log('sendData',sendData);
    $.post('/games/cycle', sendData, function(returnDataJson){
        var returnData = JSON.parse(returnDataJson);
        // console.log(returnData);
        if(returnData.complete == 'true'){
            changePosition(returnData.lastMove);
        }
    });
}

function changePosition(lastMove)
{
    console.log(lastMove);
    var index = getIndexByPieceId(lastMove.piece_id);
    console.log(lastMove.piece_id,index);
    piece = pieces[index];
    if(piece.col != lastMove.position.col || piece.row != lastMove.position.row){
        console.log('la direccion de la pieza '+piece.id+' ha cambiado');
        dropPiece(index, piece, colRowToIndex(lastMove.position.col,lastMove.position.row), false);
    }
    if(piece.direction != lastMove.position.direction){

    }
}

function dropPiece(index, piece, newBoardPosition, save)
{
    var piecePosition = colRowToIndex(piece.col, piece.row);
    board[piecePosition].occupiedBy = null;
    if(piecePosition != newBoardPosition){
        moves[piece.player]++;
        $('.score-board .player-'+piece.player+' .moves').text(moves[piece.player]);
    }
    board[newBoardPosition].occupiedBy = piece.id;
    pieces[index].col = board[newBoardPosition].col;
    pieces[index].row = board[newBoardPosition].row;
    var center = board[newBoardPosition].center();
    pieces[index].image.position = new Point(center.x, center.y);
    if(save){
        saveMove(index);
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

function findBoardPosition(x,y)
{
    var x = parseInt(x);
    var y = parseInt(y);
    var response = null;
    board.forEach(function(section,index){

        if(x>=section.xi && x<=section.xf && y>=section.yi && y<=section.yf){
            response = index;
        }
    });
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
                    if(pieces[boardSection.occupiedBy].type == 'laser'){
                        laserOthers.push(pieces[boardSection.occupiedBy]);
                        if(pieces[boardSection.occupiedBy].player == player){
                            console.log('Player '+pieces[boardSection.occupiedBy].player+' auto destroyed!');
                        }else{
                            console.log('Player '+pieces[boardSection.occupiedBy].player+' destroyed!');
                        }

                    }
                    if(pieces[boardSection.occupiedBy].type == 'mirror'){
                        laserDirection = changeLaserDirection(laserDirection, pieces[boardSection.occupiedBy].direction);
                        laserOthers.push(pieces[boardSection.occupiedBy]);
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
// console.log(path);
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
