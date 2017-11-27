@extends('layouts.app')

@section('content')



    <div class="container">
        <div class="panel panel-default">
            <div class="panel-heading">Users</div>
            <div class="panel-body">

                <table class="table table-striped users-table">
                    <thead>
                    <tr>
                        <th>Email</th>
                        <th>Full Name</th>
                        <th>Games</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->name }}</td>
                            <td><a href="{!! route('viewUser', $user->id) !!}">View</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection