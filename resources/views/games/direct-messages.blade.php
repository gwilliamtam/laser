
<div class="modal fade" id="messages-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close pull-right" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Direct Messages</h4>
                <h5 class="modal-subtitle">User name here</h5>

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

        var to = null;

        $('.send-message-link').on('click', function(){

            var link = $(this);
            $("#messages-modal .modal-subtitle").html( link.data('email') );
            to = link.data('email');
            console.log(to);
//            $("#messages-zone").html("---")

            $('#messages-modal').modal("show");
        });


        $('#send-message').on('click', function(){
            console.log('click');
            var message = $('#message-text').val();
            var type = 'dir';
            var from = '{{ Auth::user()->id }}';
            sendMessage(type, from, to, message);
        });

        function sendMessage(type, from, to, message)
        {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var sendData = {
                from: from,
                type: type,
                to: to,
                message: message
            };
            $.post('/games/message/push', sendData, function(returnVal){
            var complete = JSON.parse(returnVal);
                 if(complete == "true"){
                     $('#message-text').val('');
                 }
            });
        }

        function GetMessages()
        {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var userId = '{{ Auth::user()->id }}';
            console.log('getting messages', userId);
            var sendData = {
                userId: userId
            };
            $.post('/games/message/pull', sendData, function(returnVal){
                var data = JSON.parse(returnVal);
                if(data.complete == "true"){
                    if(data.messages != null)
                        console.log(data.messages);
                    data.messages.forEach(function(row){
                        $('#messages-zone').after(row.created_at+': '+row.message+'<br>');
                    });
                }
            });
        }

        setTimeout(GetMessages(), 3000);




    });
</script>