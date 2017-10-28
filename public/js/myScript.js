function MakeBoard()
{
    var board = [];
    var originX = 100;
    var originY = 100;
    var xi = originX;
    var yi = originY;
    var size = 50;
    var colors = ['#d0d0d0', '#f0f0f0'];
    var cnt = 0;
    for (rows = 1; rows <= 8; rows++) {
        for (cols = 1; cols <= 8; cols++) {
            cnt++;
            var xf = xi + size;
            var yf = yi + size;
            board.push(
                {
                    xi: xi,
                    yi: yi,
                    xf: xf,
                    yf: yf,
                    color: colors[cnt % 2]
                }
            );
//                    board.push( [xi,yi,xf,yf,colors[cnt % 2]] );
            xi = xi + size;
        }
        cnt--;
        yi = yi + size;
        xi = originX;
    }
    return board;
}