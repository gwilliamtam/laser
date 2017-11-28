@extends('layouts.app')

@section('title','View User')

@section('content')

    <div class="container">
        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong>{{ $user->name }}</strong>
                    <div class="pull-right">{{ $user->email }}</div>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body">

                    <ul class="nav nav-tabs" id="user-view-tabs">
                        <li role="presentation" class="active"><a id="player-a" class="games-as-player">Games as player A: {{ $user->games()->count() }}</a></li>
                        <li role="presentation" class=""><a id="player-b" href="#" class="games-as-player">Games player B: {{ $user->otherGames()->count() }}</a></li>
                    </ul>

                    <div class="row player-row player-a-row">
                        <div class="col-md-12">
                            <ul class="list-group">
                            @if ($user->games()->count() > 0)
                                @foreach ($user->games()->get() as $game)
                                    <li class="list-group-item">
                                        {{ $game->name }}<br>
                                        @if ($game->player('b')->count()>0)
                                        <span class="label label-info">Versus {{ $game->player('b')->first()['name'] }}</span>
                                        @endif
                                        <span class="label label-default">{{ $game->created_at }}</span>
                                        <span class="label label-info">{{ $game->moves()->count() }} Movements</span>
                                        @if ($game->moves()->count()>0)
                                        <span class="label label-warning">Last move by {{ strtoupper($game->moves()->first()['player']) }}, {{ ucwords( $game->getMoveTypes()[$game->moves()->first()['type']] ) }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            @else
                                    <li class="list-group-item">
                                        Games not found
                                    </li>
                            @endif
                            </ul>
                        </div>
                    </div>

                    <div class="row player-row player-b-row">
                        <div class="col-md-6">
                            <ul class="list-group">
                                @if ($user->otherGames()->count() > 0)
                                    @foreach ($user->otherGames()->get() as $otherGame)
                                        <li class="list-group-item">
                                            {{ $otherGame->name }}<br>
                                            @if ($otherGame->player('a')->count()>0)
                                                <span class="label label-info">Versus {{ $otherGame->player('a')->first()['name'] }}</span>
                                            @endif
                                            <span class="label label-default">{{ $otherGame->created_at }}</span>
                                            <span class="label label-info">{{ $otherGame->moves()->count() }} Movements</span>
                                            @if ($otherGame->moves()->count()>0)
                                                <span class="label label-warning">Last move by {{ strtoupper($otherGame->moves()->first()['player']) }}, {{ ucwords( $otherGame->getMoveTypes()[$otherGame->moves()->first()['type']] ) }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                @else
                                    <li class="list-group-item">
                                        Games not found
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="container">



    </div>

    <script>
        $(document).ready(function(){

            $('.player-a-row').show();

            $('.games-as-player').on('click', function(){
                var player = $(this);
                $('.games-as-player').parent().removeClass('active');
                player.parent().addClass('active');
                var id = player.attr('id');
                $('.player-row').hide();
                $('.'+id+'-row').show();
            })
        })
    </script>

@endsection