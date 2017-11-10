var board = null; // array containing all the sections of the board
var boardArray = new Array(); // multidimensional array used as index where each section is in index [col][row]
var pieces; // array containing all the pieces properties and images
var piecesIndex = new Array(); // cross reference between piece.id and pieces[index] position
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
var directionsArray = []; // array containing the directions
for(var key in directions){
    directionsArray.push(key);
}
var laserPaths = null; // array with each laser segment from one section to another
var laserOn = null; // flag when laser is on (showing)
var laserStop = null; // flag when laser must stop
var laserMove = null;
var draggingPiece = false; // flag when a laser is being dragged
var moves = { // array containing number of movements per player
    a: 0,
    b: 0
}
var laserDirectionChanges = { // rules on how a laser direction change when hits an mirror
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
var laserOthers = []; // array containing the pieces hit by the laser on
var sizes = { // initial sizes value to build pieces
    laser: {
        radius: 16,
        gunRadius: 8
    },
    mirror: {
        radius: 15
    }
}
var cycleExpire = 1; // seconds every cycle will be repeated
var playerInTurn = null; // current player in turn
var selectedPieceId = null;
var gameOver = null;
var admin = true;
