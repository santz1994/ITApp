@extends('layouts.app')

@section('main-content')
    <section class="content-header">
        <h1>
            User Roles Management
            <small>Manage user roles and permissions</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('/home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('users.index') }}">Users</a></li>
            <li class="active">Roles</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-shield"></i> System Roles Overview
                        </h3>
                        <div class="box-tools pull-right">
                            @can('manageRoles', App\User::class)
                                <a href="{{ route('system.roles') }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-cog"></i> Manage Roles & Permissions
                                </a>
                            @endcan
                        </div>
                    </div>
                    <div class="box-body">
                        @if(isset($roles) && count($roles) > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="rolesTable">
                                    <thead>
                                        <tr>
                                            <th>Role Name</th>
                                            <th>Display Name</th>
                                            <th>Users</th>
                                            <th>Permissions</th>
                                            <th>Created</th>
                                            <th style="width: 200px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($roles as $role)
                                        <tr>
                                            <td>
                                                <span class="label label-{{ $role->name == 'super-admin' ? 'danger' : ($role->name == 'admin' ? 'warning' : 'info') }}">
                                                    {{ $role->name }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong>{{ ucwords(str_replace('-', ' ', $role->name)) }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-blue" title="{{ $role->users->count() }} users assigned">
                                                    {{ $role->users->count() }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($role->permissions && $role->permissions->count() > 0)
                                                    <span class="badge bg-green" title="{{ $role->permissions->count() }} permissions">
                                                        {{ $role->permissions->count() }} permissions
                                                    </span>
                                                @else
                                                    <span class="text-muted"><em>No permissions</em></span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $role->created_at ? $role->created_at->format('M d, Y') : 'N/A' }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                    <!-- View Details Button -->
                                    <button type="button" class="btn btn-info" 
                                            data-toggle="modal" 
                                            data-target="#roleDetailsModal"
                                            data-role-id="{{ $role->id }}"
                                            data-role-name="{{ $role->name }}"
                                            data-role-users="{{ $role->users->count() }}"
                                            data-role-permissions="{{ $role->permissions->count() }}"
                                            data-role-users-json="{{ json_encode($role->users->map(function($u) { return ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]; })->values()) }}"
                                            data-role-permissions-json="{{ json_encode($role->permissions->map(function($p) { return ['id' => $p->id, 'name' => $p->name]; })->values()) }}"
                                            onclick="showRoleDetails(this)"
                                            title="View role details">
                                        <i class="fa fa-eye"></i> Details
                                    </button>                                                    <!-- View Users Button -->
                                                    <a href="{{ route('users.index') }}?role={{ $role->name }}" 
                                                       class="btn btn-primary"
                                                       title="View users with this role">
                                                        <i class="fa fa-users"></i> Users
                                                    </a>

                                                    @can('manageRoles', App\User::class)
                                                        <!-- Edit/Manage Button -->
                                                        <a href="{{ route('system.roles') }}" 
                                                           class="btn btn-warning"
                                                           title="Edit this role">
                                                            <i class="fa fa-edit"></i> Manage
                                                        </a>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="callout callout-info">
                                <h4><i class="fa fa-info-circle"></i> No Roles Found</h4>
                                <p>No user roles are currently defined in the system.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Statistics Cards -->
        <div class="row">
            @if(isset($roles) && count($roles) > 0)
                @foreach($roles as $role)
                <div class="col-md-6 col-lg-3">
                    <div class="box box-widget">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <span class="label label-{{ $role->name == 'super-admin' ? 'danger' : ($role->name == 'admin' ? 'warning' : 'info') }}">
                                    {{ ucwords(str_replace('-', ' ', $role->name)) }}
                                </span>
                            </h3>
                        </div>
                        <div class="box-body">
                            <dl class="row">
                                <dt class="col-sm-6">Assigned Users:</dt>
                                <dd class="col-sm-6">
                                    <strong>{{ $role->users->count() }}</strong>
                                </dd>

                                <dt class="col-sm-6">Permissions:</dt>
                                <dd class="col-sm-6">
                                    <strong>{{ $role->permissions ? $role->permissions->count() : 0 }}</strong>
                                </dd>

                                <dt class="col-sm-6">Created:</dt>
                                <dd class="col-sm-6">
                                    <small>{{ $role->created_at ? $role->created_at->format('M d, Y') : 'N/A' }}</small>
                                </dd>
                            </dl>
                        </div>
                        <div class="box-footer">
                            <div class="btn-group btn-group-sm w-100" role="group">
                                <button type="button" class="btn btn-info" 
                                        data-toggle="modal" 
                                        data-target="#roleDetailsModal"
                                        data-role-id="{{ $role->id }}"
                                        data-role-name="{{ $role->name }}"
                                        data-role-users="{{ $role->users->count() }}"
                                        data-role-permissions="{{ $role->permissions->count() }}"
                                        data-role-users-json="{{ json_encode($role->users->map(function($u) { return ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]; })->values()) }}"
                                        data-role-permissions-json="{{ json_encode($role->permissions->map(function($p) { return ['id' => $p->id, 'name' => $p->name]; })->values()) }}"
                                        onclick="showRoleDetails(this)"
                                        style="flex: 1;">
                                    <i class="fa fa-eye"></i> Details
                                </button>
                                @can('manageRoles', App\User::class)
                                    <a href="{{ route('system.roles') }}" 
                                       class="btn btn-warning"
                                       style="flex: 1;">
                                        <i class="fa fa-cog"></i> Manage
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </section>
</div>

<!-- Role Details Modal -->
<div class="modal fade" id="roleDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-shield"></i> Role Details
                </h4>
            </div>
            <div class="modal-body">
                <dl class="row" id="roleDetailsContent">
                    <dt class="col-sm-3">Role Name:</dt>
                    <dd class="col-sm-9"><strong id="detailRoleName">-</strong></dd>

                    <dt class="col-sm-3">Display Name:</dt>
                    <dd class="col-sm-9"><strong id="detailRoleDisplay">-</strong></dd>

                    <dt class="col-sm-3">Users Assigned:</dt>
                    <dd class="col-sm-9">
                        <span class="badge badge-primary" id="detailRoleUsers">0</span>
                    </dd>

                    <dt class="col-sm-3">Permissions:</dt>
                    <dd class="col-sm-9">
                        <span class="badge badge-success" id="detailRolePermissions">0</span>
                    </dd>
                </dl>

                <hr>

                <h5>Users with this role:</h5>
                <div id="roleUsersList" class="well well-sm">
                    <div class="text-center text-muted">
                        <i class="fa fa-spinner fa-spin"></i> Loading...
                    </div>
                </div>

                <h5>Permissions for this role:</h5>
                <div id="rolePermissionsList" class="well well-sm">
                    <div class="text-center text-muted">
                        <i class="fa fa-spinner fa-spin"></i> Loading...
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                @can('manageRoles', App\User::class)
                    <a href="{{ route('system.roles') }}" class="btn btn-warning">
                        <i class="fa fa-cog"></i> Edit Role
                    </a>
                @endcan
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function showRoleDetails(button) {
    const roleId = $(button).data('role-id');
    const roleName = $(button).data('role-name');
    const roleUsers = $(button).data('role-users');
    const rolePermissions = $(button).data('role-permissions');
    const usersJson = $(button).data('role-users-json');
    const permissionsJson = $(button).data('role-permissions-json');

    // Update modal title and content
    $('#detailRoleName').text(roleName);
    $('#detailRoleDisplay').text(capitalizeWords(roleName.replace('-', ' ')));
    $('#detailRoleUsers').text(roleUsers);
    $('#detailRolePermissions').text(rolePermissions);

    // Display users list
    displayRoleUsers(usersJson);

    // Display permissions list
    displayRolePermissions(permissionsJson);
}

function displayRoleUsers(usersJson) {
    if (!usersJson || usersJson.trim() === '' || usersJson === '[]') {
        $('#roleUsersList').html(
            '<p class="text-muted"><em>No users assigned to this role</em></p>'
        );
        return;
    }

    try {
        const usersArray = JSON.parse(usersJson);
        const usersList = $('<ul class="list-unstyled"></ul>');
        
        usersArray.forEach(user => {
            const userItem = $('<li>')
                .css('padding', '8px 0')
                .css('border-bottom', '1px solid #eee')
                .html(`
                    <strong>${user.name}</strong><br>
                    <small class="text-muted">${user.email}</small>
                `);
            usersList.append(userItem);
        });

        $('#roleUsersList').html(usersList);
    } catch (e) {
        console.error('Error parsing users JSON:', e);
        $('#roleUsersList').html(
            '<p class="text-danger"><em>Error loading users. Please try again.</em></p>'
        );
    }
}

function displayRolePermissions(permissionsJson) {
    if (!permissionsJson || permissionsJson.trim() === '' || permissionsJson === '[]') {
        $('#rolePermissionsList').html(
            '<p class="text-muted"><em>No permissions assigned to this role</em></p>'
        );
        return;
    }

    try {
        const permissionsArray = JSON.parse(permissionsJson);
        const permissionsList = $('<div class="row"></div>');
        
        permissionsArray.forEach(permission => {
            const permItem = $('<div class="col-md-6" style="padding: 5px 0;">')
                .html(`<span class="label label-success">${permission.name}</span>`);
            permissionsList.append(permItem);
        });

        $('#rolePermissionsList').html(permissionsList);
    } catch (e) {
        console.error('Error parsing permissions JSON:', e);
        $('#rolePermissionsList').html(
            '<p class="text-danger"><em>Error loading permissions. Please try again.</em></p>'
        );
    }
}

function capitalizeWords(str) {
    return str.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
}

// Initialize DataTable if available
$(document).ready(function() {
    if ($.fn.DataTable) {
        $('#rolesTable').DataTable({
            "paging": false,
            "ordering": true,
            "info": false,
            "searching": true,
            "columnDefs": [
                { "orderable": false, "targets": 5 }
            ]
        });
    }
});
</script>

<style>
    .label {
        padding: 5px 8px;
        border-radius: 3px;
        display: inline-block;
    }

    .label-danger { background-color: #dd4b39; color: white; }
    .label-warning { background-color: #f39c12; color: white; }
    .label-info { background-color: #5bc0de; color: white; }
    .label-primary { background-color: #0073b7; color: white; }
    
    .badge-primary { background-color: #0073b7; color: white; padding: 5px 10px; }
    .badge-success { background-color: #00a65a; color: white; padding: 5px 10px; }

    .btn-group-sm .btn {
        padding: 5px 10px;
        font-size: 12px;
    }

    .box-widget {
        margin-bottom: 20px;
    }

    .box-footer {
        display: flex;
        gap: 5px;
    }
</style>
@endsection