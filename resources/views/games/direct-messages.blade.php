
<div class="modal fade" id="messages-modal" data-game="">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close pull-right" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Game Messages Center</h4>
            </div>
            <div class="modal-body">
                <div id="messages-zone"></div>
            </div>
            <div class="modal-footer">

                <div class="input-group pull-left">
                    <input type="text" class="form-control" placeholder="Enter message" id="message-text">
                    <a class="input-group-addon btn btn-primary" id="send-message">Send</a>
                </div>

                {{--<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>--}}
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){

        var gameInfo = null;
        var gameId = null;
        var last = null;

        $('.send-message-link').on('click', function(){

            var link = $(this);
            console.log( link.data('game-info') )
            gameInfo= link.data('game-info');
            gameId = gameInfo.id;
            $('.modal-title').html(gameInfo.name);
            console.log('gameInfo', gameInfo, 'gameId', gameId);

            $('#messages-modal').modal("show");
        });


        $('#send-message').on('click', function(){
            console.log('click');
            var message = $('#message-text').val();
            var from = '{{ Auth::user()->id }}';
            sendMessage(from, message, gameId);
        });

        function sendMessage(from, message, gameId)
        {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var sendData = {
                from: from,
                message: message,
                gameId: gameId
            };
            console.log('sending', sendData);
            $.post('/games/message/push', sendData, function(returnVal){
            var complete = JSON.parse(returnVal);
                 if(complete == "true"){
                     $('#message-text').val('');
                 }
            });
        }

        setInterval(function GetMessages()
        {
            var modalOpen = $('#messages-modal').hasClass('in');
            console.log('messages modal open', modalOpen);
            if(modalOpen){
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var userId = '{{ Auth::user()->id }}';
                var gameInfo= $('#messages-modal').attr('data-game');
                var gameId = gameInfo.id;
                console.log('gameInfo', gameInfo, 'gameId', gameId)
                console.log('getting messages for game', gameId, 'last message at', last);
                var sendData = {
                    gameId: gameId,
                    last: last
                };
                $.post('/games/message/pull', sendData, function(returnVal){
                    var data = JSON.parse(returnVal);
                    if(data.complete == "true"){
                        if(data.messages != null){
                            console.log(data.messages);
                            data.messages.forEach(function(row){
                                if(last == null || row.created_at>last){
                                    if(row.from_player_id == "{{ Auth::user()->id }}"){
                                        var text = '<span class="light-text">You said</span>: ' + row.message + '<br>';
                                    }else{
                                        var text = '<span class="light-text">' + row.name + ' said</span>: ' + row.message + '<br>';
                                    }

                                    $('#messages-zone').append(text);
                                    last = row.created_at;

                                    var realHeight = $("#messages-zone")[0].scrollHeight;
                                    $("#messages-zone").scrollTop(realHeight);
                                }
                            });
                        }

                    }
                });
            }
        }, 3000);




    });
</script>