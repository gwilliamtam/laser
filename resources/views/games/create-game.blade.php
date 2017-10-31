@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Create Game</div>

                <div class="panel-body">
                    <form class="form-horizontal" method="POST" action="{{ route('createGame') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('gameName') ? ' has-error' : '' }}">
                            <label for="name" class="col-md-4 control-label">Game Name</label>

                            <div class="col-md-4">
                                <input id="gameName" type="text" class="form-control" name="gameName" value="{{ old('gameName') }}" required autofocus>

                                @if ($errors->has('gameName'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('gameName') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group class="text-center">
                            <label>{{ url('/') }}/<span id="#gameNameDisplay"></span></label>
                        </div>



                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Start Game
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
