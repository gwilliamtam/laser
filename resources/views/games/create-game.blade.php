@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Create Game</div>

                <div class="panel-body">
                        {{ csrf_field() }}

                        <div id="game-creator" class="form-group{{ $errors->has('gameName') ? ' has-error' : '' }}">
                            <label for="name" class="col-md-4 control-label">Enter desired game name</label>

                            <div class="col-md-5">
                                <input id="gameName" type="text" pattern="[a-zA-Z0-9-]+" class="form-control" name="gameName" value="{{ old('gameName') }}" required autofocus>

                                @if ($errors->has('gameName'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('gameName') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="col-md-3">
                                <button id="check" type="button" class="btn btn-primary">
                                    Check
                                </button>
                            </div>
                        </div>

                        <div id="game-exists" class="text-center">
                            <span>Game alredy exists... Try a different name...</span>
                        </div>

                        <div class="copy-link-container">

                            <div class="form-group">
                                <label for="name" class="col-md-12">Click to copy this link to clipboard and message to the other player.</label>

                                <div class="col-md-12">
                                    <input id="link" type="text" class="form-control" name="link" value="{!! url('/play') !!}/">
                                </div>
                            </div>

                            <div id="goto-game-container" class="col-md-12 text-center">
                                <button class="btn btn-primary">
                                    Goto Game
                                </button>
                            </div>
                        </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var buttonStatus = 'check';

    $(document).ready(function(){


        $('#check').on('click',function(){
            if(buttonStatus == 'check'){
                console.log('checking');
                $('#check').html('Checking...')
                buttonStatus = 'checking';
                validateName()
            }
            if(buttonStatus == 'create'){
                console.log('creating');
                $('#check').html('Creating...')
                buttonStatus = 'creating';
                createGame();
            }
        });

        $('#gameName').on('input', function(){
            $('#game-exists').hide();
        })

        $('#link').on('click', function(){
            copyLinkToClipboard();
            $('#link').addClass('link-copied');
            alert('Game link has been copied to the clipboard.');
        });

        $('#goto-game-container button').on('click', function(){
            document.location = $('#link').val();
        })

        function validateName(){
            if($('#gameName').val().length > 0){
                var gameName = $('#gameName').val();
                $.get('/games/validate/'+gameName, {},function(returnData){
                    if(returnData == 'ok'){
                        $('#check').html('Create Game');
                        buttonStatus = 'create';
                        return true;
                    }else{
                        console.log(returnData);
                        $('#check').html('Check');
                        buttonStatus = 'check'
                        $('#game-exists').show();
                    }
                });
            }
            return false;
        }

        function createGame(){
            //ajax check here
            var gameName = $('#gameName').val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.post('{!! route('createGamePost') !!}', {gameName: gameName}, function(returnData){
                console.log(returnData);
                if(returnData == 'true'){
                    $('#check').html('Game Created');
                    buttonStatus = 'created';
                    $('#check').attr('disabled','disabled');
                    $('#game-creator').hide();

                    $('#link').val('{!! url('/play') !!}/'+$('#gameName').val()) ;
                    $('.copy-link-container').show();
                }else{
                    $('#check').html('Check');
                    buttonStatus = 'check';
                }
            });
        }

        function copyLinkToClipboard() {
            var copyText = document.getElementById("link");
            copyText.select();
            document.execCommand("Copy");
        }

    });

</script>

@endsection
