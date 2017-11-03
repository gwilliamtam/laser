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
                            {{ $game->name }}
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
