@php
$metaDescription = "Play Laser Chess Game online with your friends. Move your mirrors around the board and fire your laser to destroy your enemy. Like playing chess but with power!";
$codeWords = "Laser Chess Game Online Strategy Mind Power Fire Shot Destroy Think";
$pageTitle = "Laser Chess Game";
@endphp


<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <META NAME="Description" CONTENT="{{ $metaDescription }}">
    <META NAME="Keywords" CONTENT="{{ $metaDescription }}">
    <META NAME="title" content="{{ $pageTitle }}">
    <META NAME=DC.Title content="{{ $pageTitle }}">
    <META http-equiv=title content="{{ $pageTitle }}">
    <META http-equiv=keywords content="{{ $codeWords }}">
    <META NAME=DC.Description content="{{ $metaDescription }}">
    <META http-equiv=description content="{{ $metaDescription }}">
    <META NAME="distribution" content="global">
    <META NAME="revisit" content="30 days">
    <META NAME="searchtitle" CONTENT="{{ $codeWords }}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>LaserChess - @yield('title')</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/game.css') }}" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">


    <script src="{{ asset('js/jquery-3.2.1.js') }}"></script>

</head>
<body>
    <div id="app">
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        Laser Chess
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        &nbsp;
                    </ul>

                    <!-- Right Side Of Navbar -->
                    @if ($__env->yieldContent('title')!='Page not found')
                    <ul class="nav navbar-nav navbar-right">
                        <!-- Authentication Links -->
                        @if (Auth::guest())
                            <li><a href="{{ route('howToPlay') }}">How To Play</a></li>
                            <li><a href="{{ route('login') }}">Login</a></li>
                            <li><a href="{{ route('register') }}">Register</a></li>
                        @else
                            @if( Auth::user()->isAdmin() )
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                        Admin <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu" role="menu">
                                        <li><a href="{{ route('listUsers') }}">Users</a></li>
                                    </ul>
                                </li>
                            @endif
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    Game <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu">

                                    @if(!empty($currentGame) and $currentGame->player_a_id == Auth::user()->id)
                                        <li><a href="{{ route('restartGame', [$currentGame->name, $currentGame->id]) }}">Restart</a></li>
                                    @endif
                                    <li><a href="{{ route('createGame') }}">Create</a></li>
                                    <li><a href="{{ route('home') }}">List</a></li>
                                    <li><a href="{{ route('howToPlay') }}">How To Play</a></li>
                                </ul>
                            </li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu" role="menu">
                                    <li>
                                        <a href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                    @endif
                </div>
            </div>
        </nav>

        @if(session()->has('message'))
        <div class="alert alert-success" role="alert">
            {{ session('message') }}
        </div>
        @endif

        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
