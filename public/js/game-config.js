var board;
var pieces;
// var config2 = {
//     pieceId: 0,
//     colsMax: 10,
//     rowsMax: 10,
//     sectionWidth: null,
//     sectionHeight: null,
//     board: {
//         color1: "#d0d0d0",
//         color2: "#f0f0f0"
//     },
//     player:{
//         a: {
//             color: "blue"
//         },
//         b: {
//             color: "red"
//         }
//     },
//     laser: {
//         color: "yellow",
//         width: 3
//     }
// }
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