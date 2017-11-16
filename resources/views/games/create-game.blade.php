@extends('layouts.app')

@section('title','Create Game')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Create Game</div>

                <div class="panel-body">
                        {{ csrf_field() }}

                        <div id="game-creator" class="form-group{{ $errors->has('gameName') ? ' has-error' : '' }}">

                            <p>Select game size:</p>
                            <div class="form-group game-grid text-center">
                                <button type="button" class="btn btn-default" data-size="8">8 x 8</button>
                                <button type="button" class="btn btn-primary" data-size="10">10 x 10</button>
                                <button type="button" class="btn btn-default" data-size="12">12 x 12</button>
                                <button type="button" class="btn btn-default" data-size="15">15 x 15</button>
                            </div>

                            <p>Select game shape:</p>
                            <div class="form-group game-shape text-center">
                                <button type="button" class="btn btn-primary" data-shape="twoHorizontalLines">
                                    <img src="/img/twoHorizontalLines.png" class="game-icon"><br>
                                    <span>Two Horizontal Lines</span>
                                </button>
                                <button type="button" class="btn btn-default" data-shape="triangleAroundLaser">
                                    <img src="/img/triangleAroundLaser.png" class="game-icon"><br>
                                    <span>Triangle Around Laser</span>
                                </button>
                                <button type="button" class="btn btn-default" data-shape="spreaded">
                                    <img src="/img/spreaded.png" class="game-icon"><br>
                                    <span>Spreaded</span>
                                </button>
                            </div>

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

        var newGameSize = 10;
        var newGameShape = "twoHorizontalLines";

        $('.game-grid button').on('click', function(){
            var thisBtn = $(this);
            $('.game-grid button').removeClass('btn-primary')
            $('.game-grid button').addClass('btn-default')
            thisBtn.removeClass('btn-default')
            thisBtn.addClass('btn-primary');
            newGameSize = thisBtn.data('size');
            console.log(newGameSize)
        })

        $('.game-shape button').on('click', function(){
            var thisBtn = $(this);
            $('.game-shape button').removeClass('btn-primary')
            $('.game-shape button').addClass('btn-default')
            thisBtn.removeClass('btn-default')
            thisBtn.addClass('btn-primary');
            newGameShape = thisBtn.data('shape');
            console.log(newGameShape)
        })

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

            var sendData = {
                gameName: gameName,
                userId: "{{ Auth::user()->id }}",
                size: newGameSize,
                shape: newGameShape
            }
            $.post('{!! route('createGamePost') !!}', sendData, function(returnData){
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
