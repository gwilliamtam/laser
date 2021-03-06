@extends('layouts.app')

@section('title','List Games')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                        <a type="button" class="btn btn-primary btn-lg btn-block" href="{!! route('createGame') !!}">Create a Game</a>

                </div>

                @if(!empty($games))
                <div class="panel-body">


                    <div class="pull-right allow-delete-games">
                        <button class="btn {{empty($d) ? 'btn-default' : 'btn-primary'}}">Delete Off</button>
                    </div>

                    <div>
                        <p class="text-primary">List of existent games</p>
                    </div>

                    <ul class="nav nav-tabs games-order">
                        <li role="presentation" class="{{ empty($sort) || $sort == "name" ? 'active' : null }}"><a href="/">By Name</a></li>
                        <li role="presentation" class="{{ !empty($sort) && $sort == "date" ? 'active' : null  }}"><a href="/?sort=date">By Creation Date</a></li>
                    </ul>

                    <div class="clearfix"></div>
                    <ul class="list-group games-list">
                    @foreach($games as $game)
                        @php
                            $gameSetup = json_decode($game->setup,true);
                        @endphp
                            <li class="list-group-item">

                                @if($gameStatus[$game->id] == "over")
                                    <a href="{!! route('restartGame', ['name'=>$game->name, 'id'=>$game->id]) !!}" class="btn btn-primary pull-right">Restart</a>
                                @endif

                                <a href="{!! url('/') !!}/play/{{$game->name}}" class="btn btn-primary pull-right">Play</a>

                                @if(Auth::user()->id == $game->player_a_id)
                                <button class="btn btn-primary delete-game pull-right" data-id='{"gameId":"{{$game->id}}","gameName":"{{$game->name}}","playerId":"{{Auth::user()->id}}"}'>Delete</button>
                                @endif
                                @if(Auth::user()->id == $game->player_b_id)
                                    <button class="btn btn-primary leave-game pull-right" data-id='{"gameId":"{{$game->id}}","gameName":"{{$game->name}}","playerId":"{{Auth::user()->id}}"}'>Leave</button>
                                @endif

                                @if($gameSetup['shape'] == "twoHorizontalLines")
                                    <div class="game-icon-container">
                                        <img src="/img/twoHorizontalLines.png" class="game-icon">
                                    </div>
                                @endif
                                @if($gameSetup['shape'] == "triangleAroundLaser")
                                    <div class="game-icon-container">
                                        <img src="/img/triangleAroundLaser.png" class="game-icon">
                                    </div>
                                @endif
                                @if($gameSetup['shape'] == "spreaded")
                                    <div class="game-icon-container">
                                        <img src="/img/spreaded.png" class="game-icon">
                                    </div>
                                @endif

                                <a href="{!! url('/') !!}/play/{{$game->name}}"><span class="game-name">{{ $game->name }}</span></a>
                                    ({{ $game->created_at }})
                                <br>
                                @if(array_key_exists($game->player_a_id, $usersInGames))
                                    {{ $usersInGames[$game->player_a_id]['name'] }}
                                @endif
                                &nbsp;vs&nbsp;
                                @if(array_key_exists($game->player_b_id, $usersInGames))
                                    {{ $usersInGames[$game->player_b_id]['name'] }}
                                @endif
                                <br>
                                <span class="label label-info">{{ $gameSetup['colsMax'] }} x {{ $gameSetup['rowsMax'] }}</span>
                                @if($gameStatus[$game->id] == "wait")
                                    <span class="label label-warning">Waiting for player to join game...</span>
                                    <span class="label label-warning">Send the link to the other player: {!! url('/play') !!}/{{ $game->name }}</span>
                                @endif
                                @if($gameStatus[$game->id] == "ready")
                                    <span class="label label-success">Ready</span>
                                @endif
                                @if($gameStatus[$game->id] == "over")
                                    <span class="label label-danger">Game Over</span>
                                @endif
                                <a class="send-message-link" href="#" data-game-info='{ "id": "{{ $game->id }}", "name": "{{ $game->name }}"}'>
                                    <span class="label label-primary"><i class="fa fa-envelope-o" aria-hidden="true"></i></span>
                                </a>
                                <div class="clearfix"></div>
                            </li>
                    @endforeach
                    </ul>
                </div>
                @endif
            </div>

        </div>
    </div>
</div>

@include("games.direct-messages")

<script>
    $(document).ready(function(){
        $('button.delete-game').on('click', function(){
            var thisButton = $(this);
            thisButton.addClass('disabled');
            thisButton.html('Deleting...');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post( "{!! route('deleteGame') !!}", thisButton.data('id') ).done(function(){
                document.location = "{!! route('home') !!}";
            });
        });

        $('button.leave-game').on('click', function(){
            var thisButton = $(this);
            thisButton.addClass('disabled');
            thisButton.html('Leaving...');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post( "{!! route('leaveGame') !!}", thisButton.data('id') ).done(function(){
                document.location = "{!! route('home') !!}";
            });
        });

        $('.allow-delete-games .btn').on('click',function(){
            var thisButton = $(this);
            if(thisButton.hasClass('btn-default')){
                thisButton.removeClass('btn-default');
                thisButton.html('Delete On');
                thisButton.addClass('btn-primary');
                $('.delete-game').show();
                $('.leave-game').show();
            }else{
                thisButton.removeClass('btn-primary');
                thisButton.html('Delete Off');
                thisButton.addClass('btn-default');
                $('.delete-game').hide();
                $('.leave-game').hide();
            }
        })

    })
</script>
@endsection
