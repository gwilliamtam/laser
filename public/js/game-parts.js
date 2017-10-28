
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

function MakePieces()
{
    var pieces = [];

    pieces.add = function(type, position, player, direction)
    {
        var pair = position.split(',');
        var col = parseInt(pair[0])
        var row = parseInt(pair[1])
        var boardIndex = colRowToIndex(col, row);

        if(boardIndex.occupiedBy == null){
            var piece = {
                id: config.pieceId,
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
    pieces.setImage = function(id, image)
    {
        pieces.forEach(function(piece,index){
            if(piece.id == id){
                piece.image = image;
            }
        })
    }
    return pieces;
}

function paintPiece(piece)
{

    if(piece.type == 'laser'){
        paintLaser(piece);
    }
    if(piece.type == 'pole'){
        var boardIndex = colRowToIndex(piece.col, piece.row);
        var center = board[boardIndex].center();
        var pieceColor;
        if(piece.player == 'a'){
            pieceColor = 'black';
        }else{
            pieceColor = 'red';
        }
        // var myCircle = new Path.Circle(new Point(center.x, center.y), 10);
        // myCircle.fillColor = pieceColor;

        var long = 15;
        var pole = new Path.Arc(
            new Point(center.x-long, center.y),
            new Point(center.x, center.y+long),
            new Point(center.x+long, center.y)
        )
        pole.fillColor = pieceColor;
        pole.rotate(directions[piece.direction]);
        pole.position = new Point(center.x, center.y);

        pieces.setImage(piece.id, pole);
    }
}

function rotatePole(piece, rotationDir)
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
    pieces[piece.id].direction = directionsArray[newDirIndex];

    piece.image.rotate(angle);

}

function paintLaser(piece)
{
    var boardIndex = colRowToIndex(piece.col, piece.row);
    var center = board[boardIndex].center();
    var pieceColor;
    if(piece.player == 'a'){
        pieceColor = 'black';
    }else{
        pieceColor = 'red';
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
    pieces.setImage(piece.id, path);
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
    var colors = ['#d0d0d0', '#f0f0f0'];
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
        return {x:x-gap, y:y};
    }
    if(direction=='o'){
        return {x:x+gap, y:y};
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

function movePiece(piece, delta)
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
        pieces[piece.id].image.position = new Point(newX, newY);
        return newBoardPosition;
    }else{
        return colRowToIndex(piece.col, piece.row);
    }
}

function dropPiece(piece, newBoardPosition)
{
    var piecePosition = colRowToIndex(piece.col, piece.row);
    board[piecePosition].occupiedBy = null;
    if(piecePosition != newBoardPosition){
        moves[piece.player]++;
        $('.score-board .player-'+piece.player+' .moves').text(moves[piece.player]);
    }
    board[newBoardPosition].occupiedBy = piece.id;
    pieces[piece.id].col = board[newBoardPosition].col;
    pieces[piece.id].row = board[newBoardPosition].row;
    var center = board[newBoardPosition].center();
    pieces[piece.id].image.position = new Point(center.x, center.y);
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
                    if(pieces[boardSection.occupiedBy].type == 'laser' && pieces[boardSection.occupiedBy].player != player){
                        console.log('TARGET!');
                    }
                    if(pieces[boardSection.occupiedBy].type == 'pole'){
                        laserDirection = changeLaserDirection(laserDirection, pieces[boardSection.occupiedBy].direction);
                    }
                    if(laserDirection != null){

                        laserPaths.push(calcLaserPath(prev, next));
                        laserSegment++;
                        calculateTrack = true;
                    }
                }

            }
            if(next == null){
                console.log('laser ends in ',prev);
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
        strokeColor: 'red',
        strokeWidth: 5
    });
    return laserPath;
}

function changeLaserDirection(laserDirection, poleDirection)
{
    var group = laserDirectionChanges[laserDirection];
    if(group.hasOwnProperty(poleDirection)){
        return group[poleDirection];
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

}

function MakePoleControls()
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
    // left.visible = false;
    left.position =new Point(center.x-15, center.y-12);
    // left.fillColor.alpha = 0.5;

    var right = new Path.RegularPolygon(new Point(center.x+15, center.y), 3, 10);
    right.rotate(90);
    right.fillColor = 'black';
    // right.visible = false;
    right.position = new Point(center.x+15, center.y-12);
    // right.fillColor.alpha = 0.5;

    // left.moveAbove(bg);
    right.moveAbove(bg);

    var controlBoard = new Group([bg, left, right]);
    controlBoard.visible = false;

    return controlBoard;
}

function showControl(piece, poleControls)
{
    var controlsCenter = board[colRowToIndex(piece.col, piece.row)].center();
    console.log(poleControls,controlsCenter);
    poleControls.position = new Point(controlsCenter.x, controlsCenter.y);
    poleControls.visible = true;
    poleControls.moveAbove(piece.image);
}

function hidePoleControl()
{
    poleControls.visible = false;
}