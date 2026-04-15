@extends('layouts.app')

@section('main-content')
    {{-- Modern Page Header --}}
    @include('components.page-header', [
        'title' => 'Roles Management',
        'subtitle' => 'Manage user roles and their permissions',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'System Settings', 'url' => route('system.settings')],
            ['label' => 'Roles', 'url' => null]
        ],
        'actions' => '
            <a href="'.route('system.permissions').'" class="btn btn-primary">
                <i class="fa fa-key"></i> Manage Permissions
            </a>
            <button type="button" class="btn btn-warning" onclick="clearCache()">
                <i class="fa fa-refresh"></i> Clear Cache
            </button>
        '
    ])

    <section class="content container-fluid">
        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
        </div>
        @endif

        {{-- Quick Stats Dashboard --}}
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>{{ $roles->count() }}</h3>
                        <p>Total Roles</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-shield"></i>
                    </div>
                    <a href="#roles-overview" class="small-box-footer">
                        View Details <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{{ $roles->sum('permissions_count') }}</h3>
                        <p>Total Permissions</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-key"></i>
                    </div>
                    <a href="{{ route('system.permissions') }}" class="small-box-footer">
                        Manage <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3>{{ $users->count() }}</h3>
                        <p>Active Users</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-users"></i>
                    </div>
                    <a href="{{ route('users.index') }}" class="small-box-footer">
                        View Users <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>{{ $users->filter(fn($u) => $u->roles->isEmpty())->count() }}</h3>
                        <p>Unassigned Users</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-exclamation-triangle"></i>
                    </div>
                    <a href="{{ route('users.index') }}" class="small-box-footer">
                        Assign Roles <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Roles Overview --}}
        <div class="row" id="roles-overview">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-shield-alt"></i> System Roles
                            <span class="badge bg-light-blue">{{ $roles->count() }}</span>
                        </h3>
                        <div class="box-tools pull-right">
                            @can('create', App\Role::class)
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#createRoleModal" style="margin-right: 5px;">
                                    <i class="fa fa-plus"></i> Create New Role
                                </button>
                            @endcan
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">
                            <i class="fa fa-info-circle"></i> Overview of all system roles with their assigned users and permissions.
                        </p>
                        <div class="row">
                            @foreach($roles as $role)
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <div class="info-box hover-shadow" style="cursor: pointer;" onclick="scrollToRole('role-{{ $role->id }}')">
                                    <span class="info-box-icon bg-{{ $role->name === 'super-admin' ? 'red' : ($role->name === 'admin' ? 'yellow' : ($role->name === 'management' ? 'aqua' : ($role->name === 'director' ? 'navy' : ($role->name === 'receptionist' ? 'purple' : 'green')))) }}">
                                        <i class="fa fa-{{ $role->name === 'super-admin' ? 'crown' : ($role->name === 'admin' ? 'user-tie' : ($role->name === 'management' ? 'briefcase' : ($role->name === 'director' ? 'user-graduate' : ($role->name === 'receptionist' ? 'concierge-bell' : 'user')))) }}"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">{{ $role->display_name ?? ucfirst(str_replace('-', ' ', $role->name)) }}</span>
                                        <span class="info-box-number">
                                            <strong>{{ $role->users_count }}</strong> users
                                            <br><small class="text-muted">{{ $role->permissions_count }} permissions</small>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Role Details --}}
        <div class="row">
            <div class="col-md-6">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-key"></i> Role Permissions Matrix
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">
                            <i class="fa fa-info-circle"></i> Each role's assigned permissions. Click to view full details.
                        </p>
                        
                        @foreach($roles as $role)
                        <div class="box box-widget collapsed-box" id="role-{{ $role->id }}" style="margin-bottom: 10px;">
                            <div class="box-header with-border" style="cursor: pointer; display: flex; align-items: center; justify-content: space-between;" data-widget="collapse">
                                <div style="flex: 0 0 auto;">
                                    <span class="label label-{{ $role->name === 'super-admin' ? 'danger' : ($role->name === 'admin' ? 'warning' : ($role->name === 'management' ? 'info' : 'success')) }}">
                                        <i class="fa fa-{{ $role->name === 'super-admin' ? 'crown' : ($role->name === 'admin' ? 'user-tie' : ($role->name === 'management' ? 'briefcase' : 'user')) }}"></i>
                                        {{ $role->display_name ?? ucfirst(str_replace('-', ' ', $role->name)) }}
                                    </span>
                                </div>
                                <div style="flex: 1; display: flex; justify-content: center; gap: 8px;">
                                    <span class="badge bg-light-blue">
                                        {{ $role->permissions->count() }} permissions
                                    </span>
                                    <span class="badge bg-gray">
                                        {{ $role->users_count }} users
                                    </span>
                                </div>
                                <div class="box-tools" style="flex: 0 0 auto;">
                                    <div class="btn-group btn-group-sm" role="group">
                                        @can('update', $role)
                                            <button type="button" class="btn btn-sm btn-primary" onclick="editRole({{ $role->id }}); event.stopPropagation();" title="Edit Role">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                        @endcan
                                        @can('delete', $role)
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteRole({{ $role->id }}, '{{ $role->name }}'); event.stopPropagation();" title="Delete Role">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse" style="margin-left: 5px;">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="box-body" style="display: none;">
                                @if($role->permissions->count() > 0)
                                    <div class="row">
                                        @foreach($role->permissions as $permission)
                                        <div class="col-md-6" style="margin-bottom: 5px;">
                                            <i class="fa fa-check-circle text-success"></i>
                                            <span class="label label-default">{{ $permission->name }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center text-muted" style="padding: 20px;">
                                        <i class="fa fa-exclamation-triangle fa-2x" style="opacity: 0.3;"></i>
                                        <p>No permissions assigned to this role</p>
                                        <a href="{{ route('system.permissions') }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-plus"></i> Assign Permissions
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-users"></i> User Role Assignments
                            <span class="badge bg-green">{{ $users->count() }}</span>
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">
                            <i class="fa fa-info-circle"></i> All users with their assigned roles. 
                            @if($users->filter(fn($u) => $u->roles->isEmpty())->count() > 0)
                                <span class="text-danger">
                                    <strong>{{ $users->filter(fn($u) => $u->roles->isEmpty())->count() }}</strong> users without roles!
                                </span>
                            @endif
                        </p>
                        
                        @if($users->count() > 0)
                        <div class="table-responsive">
                            <table id="users-table" class="table table-enhanced table-striped table-hover table-bordered" style="opacity: 0; transition: opacity 0.3s;">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;"><i class="fa fa-hashtag"></i></th>
                                        <th><i class="fa fa-user"></i> User</th>
                                        <th><i class="fa fa-shield"></i> Roles</th>
                                        <th style="width: 100px;"><i class="fa fa-cogs"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                    <tr>
                                        <td class="text-center"><strong>{{ $user->id }}</strong></td>
                                        <td>
                                            <strong>{{ $user->name }}</strong>
                                            <br><small class="text-muted">
                                                <i class="fa fa-envelope"></i> {{ $user->email }}
                                            </small>
                                            @if($user->division)
                                            <br><small class="text-info">
                                                <i class="fa fa-building"></i> {{ $user->division->name }}
                                            </small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->roles->count() > 0)
                                                @foreach($user->roles as $role)
                                                <span class="label label-{{ $role->name === 'super-admin' ? 'danger' : ($role->name === 'admin' ? 'warning' : ($role->name === 'management' ? 'info' : 'success')) }}">
                                                    @if($role->name === 'super-admin')
                                                        <i class="fa fa-crown"></i>
                                                    @elseif($role->name === 'admin')
                                                        <i class="fa fa-user-tie"></i>
                                                    @else
                                                        <i class="fa fa-user"></i>
                                                    @endif
                                                    {{ $role->display_name ?? ucfirst(str_replace('-', ' ', $role->name)) }}
                                                </span>
                                                @endforeach
                                            @else
                                                <span class="label label-danger">
                                                    <i class="fa fa-exclamation-triangle"></i> No Role
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-xs btn-primary" title="Edit User">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center text-muted" style="padding: 40px;">
                            <i class="fa fa-users fa-3x" style="opacity: 0.3;"></i>
                            <h4>No Users Found</h4>
                            <p>No users are registered in the system yet.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Role Hierarchy Information --}}
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-sitemap"></i> Role Hierarchy & Descriptions
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body" style="display: none;">
                        <div class="alert alert-info">
                            <h4><i class="fa fa-info-circle"></i> Understanding Role Hierarchy</h4>
                            <p>Roles define what users can access and do within the system. Each role has specific permissions assigned.</p>
                        </div>
                        
                        <div class="row">
                            @foreach($roles as $role)
                            <div class="col-md-6">
                                <div class="callout callout-{{ $role->name === 'super-admin' ? 'danger' : ($role->name === 'admin' ? 'warning' : ($role->name === 'management' ? 'info' : 'success')) }}">
                                    <h4>
                                        <i class="fa fa-{{ $role->name === 'super-admin' ? 'crown' : ($role->name === 'admin' ? 'user-tie' : ($role->name === 'management' ? 'briefcase' : 'user')) }}"></i>
                                        {{ $role->display_name ?? ucfirst(str_replace('-', ' ', $role->name)) }}
                                    </h4>
                                    <p>{{ $role->description ?? 'No description available' }}</p>
                                    <ul>
                                        <li><strong>Users:</strong> {{ $role->users_count }}</li>
                                        <li><strong>Permissions:</strong> {{ $role->permissions_count }}</li>
                                    </ul>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="row">
            <div class="col-md-12">
                <div class="box box-solid">
                    <div class="box-header with-border bg-gray">
                        <h3 class="box-title">
                            <i class="fa fa-bolt"></i> Quick Actions
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="btn-group btn-group-justified" role="group">
                            <a href="{{ route('system.permissions') }}" class="btn btn-app bg-light-blue">
                                <i class="fa fa-key"></i> Permissions
                            </a>
                            <a href="{{ route('users.index') }}" class="btn btn-app bg-green">
                                <i class="fa fa-users"></i> Users
                            </a>
                            <a href="{{ route('system.settings') }}" class="btn btn-app bg-orange">
                                <i class="fa fa-cogs"></i> Settings
                            </a>
                            <button type="button" class="btn btn-app bg-yellow" onclick="clearCache()">
                                <i class="fa fa-refresh"></i> Clear Cache
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

{{-- Create Role Modal --}}
<div class="modal fade" id="createRoleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('system.roles.store') }}" method="POST" id="createRoleForm">
                @csrf
                <div class="modal-header bg-success">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">
                        <i class="fa fa-plus-circle"></i> Create New Role
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> <strong>Note:</strong> Only Super Administrators can create new roles.
                    </div>

                    <div class="form-group">
                        <label for="create_name">Role Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="create_name" name="name" 
                               placeholder="e.g., developer, accountant, hr-manager" required 
                               pattern="[a-zA-Z0-9\-_]+" 
                               title="Only letters, numbers, dashes and underscores allowed">
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i> Use lowercase letters, numbers, dashes and underscores only. No spaces.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="create_display_name">Display Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="create_display_name" name="display_name" 
                               placeholder="e.g., Developer, Accountant, HR Manager" required maxlength="255">
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i> Human-readable name shown in the interface.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="create_description">Description</label>
                        <textarea class="form-control" id="create_description" name="description" 
                                  rows="3" maxlength="500" 
                                  placeholder="Describe what this role is for and what responsibilities it has..."></textarea>
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i> Optional. Maximum 500 characters.
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Permissions</label>
                        <div class="row">
                            @foreach($permissions as $permission)
                            <div class="col-md-6">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}">
                                        <strong>{{ $permission->name }}</strong>
                                        @if($permission->description)
                                            <br><small class="text-muted">{{ $permission->description }}</small>
                                        @endif
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i> Select permissions to assign to this role. You can change this later.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> Create Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Role Modal --}}
<div class="modal fade" id="editRoleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="" method="POST" id="editRoleForm">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">
                        <i class="fa fa-edit"></i> Edit Role
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i> <strong>Warning:</strong> Changing role permissions will affect all users with this role.
                    </div>

                    <input type="hidden" id="edit_role_id" name="role_id">

                    <div class="form-group">
                        <label for="edit_name">Role Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" 
                               placeholder="e.g., developer, accountant, hr-manager" required 
                               pattern="[a-zA-Z0-9\-_]+" 
                               title="Only letters, numbers, dashes and underscores allowed">
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i> Use lowercase letters, numbers, dashes and underscores only. No spaces.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="edit_display_name">Display Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_display_name" name="display_name" 
                               placeholder="e.g., Developer, Accountant, HR Manager" required maxlength="255">
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i> Human-readable name shown in the interface.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" 
                                  rows="3" maxlength="500" 
                                  placeholder="Describe what this role is for and what responsibilities it has..."></textarea>
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i> Optional. Maximum 500 characters.
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Permissions</label>
                        <div class="row" id="edit_permissions_container">
                            @foreach($permissions as $permission)
                            <div class="col-md-6">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="edit-permission-checkbox">
                                        <strong>{{ $permission->name }}</strong>
                                        @if($permission->description)
                                            <br><small class="text-muted">{{ $permission->description }}</small>
                                        @endif
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i> Select permissions to assign to this role.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fa fa-save"></i> Update Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Edit Role Function
function editRole(roleId) {
    // Fetch role data
    fetch(`/system/roles/${roleId}/edit`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(role => {
        // Populate form
        document.getElementById('edit_role_id').value = role.id;
        document.getElementById('edit_name').value = role.name;
        document.getElementById('edit_display_name').value = role.display_name || '';
        document.getElementById('edit_description').value = role.description || '';
        
        // Update form action
        document.getElementById('editRoleForm').action = `/system/roles/${role.id}`;
        
        // Uncheck all permissions first
        document.querySelectorAll('.edit-permission-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Check assigned permissions
        if (role.permissions && role.permissions.length > 0) {
            role.permissions.forEach(permission => {
                const checkbox = document.querySelector(`.edit-permission-checkbox[value="${permission.id}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
        }
        
        // Show modal
        $('#editRoleModal').modal('show');
    })
    .catch(error => {
        alert('❌ Error loading role data: ' + error.message);
    });
}

// Delete Role Function
function deleteRole(roleId, roleName) {
    if (confirm(`Are you sure you want to delete the role "${roleName}"?\n\n⚠️ This action cannot be undone.\n\n✓ Users with this role will need to be assigned a different role.\n✓ System roles (super-admin, admin, management, user) cannot be deleted.`)) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/system/roles/${roleId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Clear Cache Function
function clearCache() {
    if (confirm('Are you sure you want to clear all caches? This may temporarily slow down the application.')) {
        fetch('{{ route("system.cache.clear") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message);
                location.reload();
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(error => {
            alert('❌ Error: ' + error.message);
        });
    }
}

// Smooth scroll to role section
function scrollToRole(roleId) {
    const element = document.getElementById(roleId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Expand the collapsed box
        const boxElement = $(element);
        if (boxElement.hasClass('collapsed-box')) {
            boxElement.find('[data-widget="collapse"]').click();
        }
        
        // Highlight briefly
        boxElement.addClass('highlight-pulse');
        setTimeout(() => {
            boxElement.removeClass('highlight-pulse');
        }, 2000);
    }
}

// Initialize DataTables and enhancements on document ready
$(document).ready(function() {
    // Defer DataTable initialization to prevent blocking
    setTimeout(function() {
        if ($('#users-table').length) {
            // Check if table has many rows
            var rowCount = $('#users-table tbody tr').length;
            var useOptimizedConfig = rowCount > 50;
            
            $('#users-table').DataTable({
                "responsive": false,
                "pageLength": useOptimizedConfig ? 50 : 25,
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "order": [[0, "asc"]],
                "deferRender": true,
                "processing": false,
                "autoWidth": false,
                "stateSave": false,
                "search": { smart: false },
                "language": {
                    "search": "_INPUT_",
                    "searchPlaceholder": "Search users...",
                    "lengthMenu": "Show _MENU_",
                    "info": "_START_-_END_ of _TOTAL_",
                    "infoEmpty": "No users",
                    "infoFiltered": "(filtered from _MAX_)",
                    "emptyTable": "No users found",
                    "zeroRecords": "No matching users"
                },
                "dom": '<"row"<"col-sm-6"l><"col-sm-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-5"i><"col-sm-7"p>>',
                "columnDefs": [
                    { "targets": [0], "className": "text-center", "width": "40px" },
                    { "targets": [3], "orderable": false, "searchable": false, "className": "text-center", "width": "100px" }
                ],
                "initComplete": function() {
                    // Fade in table after initialization
                    $('#users-table').css('opacity', '1');
                }
            });
        }
    }, 100); // Increased delay to allow page to render first
});
</script>

<style>
/* Hover effect for info-boxes (using pure CSS for better performance) */
.info-box.hover-shadow {
    transition: all 0.3s ease;
}

.info-box.hover-shadow:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.2) !important;
    transform: translateY(-2px) !important;
}

/* Highlight pulse animation */
@keyframes highlightPulse {
    0%, 100% { background-color: transparent; }
    50% { background-color: rgba(255, 193, 7, 0.3); }
}

.highlight-pulse {
    animation: highlightPulse 2s ease;
}

/* Better spacing for role boxes */
.box-widget {
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border-radius: 3px;
}

/* Callout enhancements */
.callout {
    border-left-width: 5px;
}

.callout h4 {
    margin-top: 0;
}

.callout ul {
    margin-bottom: 0;
}
</style>
@endsection
