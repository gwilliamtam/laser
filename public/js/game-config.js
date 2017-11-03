var board;
var pieces;
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
var laserOthers = [];
var sizes = {
    laser: {
        radius: 16,
        gunRadius: 8
    },
    mirror: {
        radius: 15
    }
}
