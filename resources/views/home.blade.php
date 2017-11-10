@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                        <a type="button" class="btn btn-primary btn-lg btn-block" href="{!! route('createGame') !!}">Create a Game</a>

                    @if(!empty($games))
                        <a type="button" class="btn btn-primary btn-lg btn-block">Go to existent game</a>
                    @endif

                </div>

                @if(!empty($games))
                <div class="panel-body">
                    <div class="list-group">
                    @foreach($games as $game)
                        <a class="list-group-item" href="{!! url('/') !!}/play/{{$game->name}}">
                            <strong>{{ $game->name }}</strong>
                            @if(array_key_exists($game->player_a_id, $usersInGames))
                                / {{ $usersInGames[$game->player_a_id]['name'] }}
                            @endif
                            @if(array_key_exists($game->player_b_id, $usersInGames))
                                / {{ $usersInGames[$game->player_b_id]['name'] }}
                            @endif

                            @if($gameStatus[$game->id] == "wait")
                                <span class="label label-primary pull-right">Waiting for player to join game...</span>
                            @endif
                            @if($gameStatus[$game->id] == "ready")
                                <span class="label label-success pull-right">Ready</span>
                            @endif
                            @if($gameStatus[$game->id] == "over")
                                <span class="label label-warning pull-right">Game Over</span>
                            @endif
                        </a>
                    @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
