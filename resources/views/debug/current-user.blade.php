@extends('layouts.app')

@section('main-content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Current User Debug Info</h3>
        </div>
        <div class="card-body">
            @auth
                <dl class="row">
                    <dt class="col-sm-3">User Name</dt>
                    <dd class="col-sm-9"><strong>{{ Auth::user()->name }}</strong></dd>

                    <dt class="col-sm-3">User ID</dt>
                    <dd class="col-sm-9">{{ Auth::user()->id }}</dd>

                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9">{{ Auth::user()->email }}</dd>

                    <dt class="col-sm-3">Roles</dt>
                    <dd class="col-sm-9">
                        @php $roles = Auth::user()->getRoleNames()->toArray(); @endphp
                        @if(count($roles) > 0)
                            @foreach($roles as $role)
                                <span class="badge badge-primary">{{ $role }}</span>
                            @endforeach
                        @else
                            <span class="text-muted">No roles assigned</span>
                        @endif
                    </dd>

                    <dt class="col-sm-3">Is Admin?</dt>
                    <dd class="col-sm-9">
                        @if(in_array('admin', $roles) || in_array('super-admin', $roles))
                            <span class="badge badge-success">YES</span>
                        @else
                            <span class="badge badge-danger">NO</span>
                        @endif
                    </dd>
                </dl>

                <hr>

                <h4>All Users with Admin/Super-Admin Roles:</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Roles</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(\App\User::with('roles')->get() as $user)
                            @if($user->hasAnyRole(['admin', 'super-admin']))
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @foreach($user->getRoleNames() as $role)
                                            <span class="badge badge-info">{{ $role }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="alert alert-warning">
                    You are not logged in!
                </div>
            @endauth
        </div>
    </div>
</div>
@endsection
