@extends('layouts.app')

@section('content')
    <div class="container">
        <div id="config">xxx</div>
        <script>
            var config = "willy";
            var config = JSON.parse('{!! htmlspecialchars_decode( $config ) !!}');
            console.log("config", config)
            $('#config').html(config);
        </script>
    </div>
@endsection