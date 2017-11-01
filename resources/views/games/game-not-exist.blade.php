@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="alert alert-danger" role="alert">
            <h4>Game No Exist</h4>
            <p>The game {{ $gameName }} not exist. You can <a href="{!! route('createGame') !!}">click here</a> to create a game.</p>

        </div>
    </div>
@endsection