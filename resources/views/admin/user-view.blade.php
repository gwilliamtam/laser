@extends('layouts.app')

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
                    <div class="row">
                        <div class="col-md-3">
                            Games as player A: <strong>{{ $user->games()->count() }}</strong>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-group">
                            @if ($user->games()->count() > 0)
                                @foreach ($user->games()->get() as $game)
                                    <li class="list-group-item">
                                        {{ $game->name }}<br>
                                        {{ $game }}
                                        @if ($game->player('b')->count()>0)
                                        <span class="label label-info">Versus {{ $game->player('b')->first()['name'] }}</span>
                                        @endif
                                        <span class="label label-default">{{ $game->created_at }}</span>
                                        <span class="label label-info">Moves {{ $game->moves()->count() }}</span>
                                        @if ($game->moves()->count()>0)
                                        <span class="label label-warning">Last move by {{ strtoupper($game->moves()->first()['player']) }} type {{ strtoupper($game->moves()->first()['type']) }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            @endif
                            </ul>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            Games player B <strong>{{ $user->otherGames()->count() }}</strong>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-group">
                                @if ($user->otherGames()->count() > 0)
                                    @foreach ($user->otherGames()->get() as $otherGame)
                                        <li class="list-group-item">
                                            {{ $otherGame->name }} {{ $otherGame->created_at }}
                                        </li>
                                    @endforeach
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

@endsection