@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-3">
            Id:
            </div>
            <div class="col-md-3">
                {{ $user->id }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
            Email:
            </div>
            <div class="col-md-3">
            {{ $user->email }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
            Name:
            </div>
            <div class="col-md-3">
            {{ $user->name }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
            Games:
            </div>
            <div class="col-md-3">
            {{ $user->games()->count() }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
            Other Games:
            </div>
            <div class="col-md-3">
            {{ $user->otherGames()->count() }}
            </div>
        </div>


    </div>

@endsection