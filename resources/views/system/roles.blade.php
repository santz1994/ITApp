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

    <div class="container-fluid" style="margin-top: 8px; margin-bottom: 12px;">
        <div class="row">
            <div class="col-md-12 text-right">
                <div class="btn-group btn-group-xs" role="group" aria-label="System Roles Language Toggle">
                    <button type="button" class="btn btn-default" id="systemRolesLanguageEnglish" data-lang="en">EN</button>
                    <button type="button" class="btn btn-default" id="systemRolesLanguageIndonesian" data-lang="id">ID</button>
                </div>
            </div>
        </div>
    </div>

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
                        <p data-i18n="system.roles.stats.total_roles">Total Roles</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-shield"></i>
                    </div>
                    <a href="#roles-overview" class="small-box-footer">
                        <span data-i18n="system.roles.stats.view_details">View Details</span> <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{{ $roles->sum('permissions_count') }}</h3>
                        <p data-i18n="system.roles.stats.total_permissions">Total Permissions</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-key"></i>
                    </div>
                    <a href="{{ route('system.permissions') }}" class="small-box-footer">
                        <span data-i18n="system.roles.stats.manage">Manage</span> <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3>{{ $users->count() }}</h3>
                        <p data-i18n="system.roles.stats.active_users">Active Users</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-users"></i>
                    </div>
                    <a href="{{ route('users.index') }}" class="small-box-footer">
                        <span data-i18n="system.roles.stats.view_users">View Users</span> <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>{{ $users->filter(fn($u) => $u->roles->isEmpty())->count() }}</h3>
                        <p data-i18n="system.roles.stats.unassigned_users">Unassigned Users</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-exclamation-triangle"></i>
                    </div>
                    <a href="{{ route('users.index') }}" class="small-box-footer">
                        <span data-i18n="system.roles.stats.assign_roles">Assign Roles</span> <i class="fa fa-arrow-circle-right"></i>
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
                            <i class="fa fa-shield-alt"></i> <span data-i18n="system.roles.section.system_roles">System Roles</span>
                            <span class="badge bg-light-blue">{{ $roles->count() }}</span>
                        </h3>
                        <div class="box-tools pull-right">
                            @can('create', App\Role::class)
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#createRoleModal" style="margin-right: 5px;">
                                    <i class="fa fa-plus"></i> <span data-i18n="system.roles.action.create_new_role">Create New Role</span>
                                </button>
                            @endcan
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">
                            <i class="fa fa-info-circle"></i> <span data-i18n="system.roles.section.system_roles_help">Overview of all system roles with their assigned users and permissions.</span>
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
                                            <strong>{{ $role->users_count }}</strong> <span data-i18n="system.roles.role_card.users_suffix">users</span>
                                            <br><small class="text-muted">{{ $role->permissions_count }} <span data-i18n="system.roles.role_card.permissions_suffix">permissions</span></small>
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
                            <i class="fa fa-key"></i> <span data-i18n="system.roles.section.permissions_matrix">Role Permissions Matrix</span>
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">
                            <i class="fa fa-info-circle"></i> <span data-i18n="system.roles.section.permissions_matrix_help">Each role's assigned permissions. Click to view full details.</span>
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
                                        {{ $role->permissions->count() }} <span data-i18n="system.roles.role_card.permissions_suffix">permissions</span>
                                    </span>
                                    <span class="badge bg-gray">
                                        {{ $role->users_count }} <span data-i18n="system.roles.role_card.users_suffix">users</span>
                                    </span>
                                </div>
                                <div class="box-tools" style="flex: 0 0 auto;">
                                    <div class="btn-group btn-group-sm" role="group">
                                        @can('update', $role)
                                            <button type="button" class="btn btn-sm btn-primary" onclick="editRole({{ $role->id }}); event.stopPropagation();" data-i18n-title="system.roles.action.edit_role_title" title="Edit Role">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                        @endcan
                                        @can('delete', $role)
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteRole({{ $role->id }}, '{{ $role->name }}'); event.stopPropagation();" data-i18n-title="system.roles.action.delete_role_title" title="Delete Role">
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
                                        <p data-i18n="system.roles.role_card.no_permissions">No permissions assigned to this role</p>
                                        <a href="{{ route('system.permissions') }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-plus"></i> <span data-i18n="system.roles.action.assign_permissions">Assign Permissions</span>
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
                            <i class="fa fa-users"></i> <span data-i18n="system.roles.section.user_assignments">User Role Assignments</span>
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
                            <i class="fa fa-info-circle"></i> <span data-i18n="system.roles.section.user_assignments_help">All users with their assigned roles.</span>
                            @if($users->filter(fn($u) => $u->roles->isEmpty())->count() > 0)
                                <span class="text-danger">
                                    <strong>{{ $users->filter(fn($u) => $u->roles->isEmpty())->count() }}</strong> <span data-i18n="system.roles.section.users_without_roles">users without roles!</span>
                                </span>
                            @endif
                        </p>
                        
                        @if($users->count() > 0)
                        <div class="table-responsive">
                            <table id="users-table" class="table table-enhanced table-striped table-hover table-bordered" style="opacity: 0; transition: opacity 0.3s;">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;"><i class="fa fa-hashtag"></i></th>
                                        <th><i class="fa fa-user"></i> <span data-i18n="system.roles.table.user">User</span></th>
                                        <th><i class="fa fa-shield"></i> <span data-i18n="system.roles.table.roles">Roles</span></th>
                                        <th style="width: 100px;"><i class="fa fa-cogs"></i> <span data-i18n="system.roles.table.actions">Actions</span></th>
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
                                                    <i class="fa fa-exclamation-triangle"></i> <span data-i18n="system.roles.table.no_role">No Role</span>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-xs btn-primary" data-i18n-title="system.roles.action.edit_user_title" title="Edit User">
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
                            <h4 data-i18n="system.roles.table.no_users_title">No Users Found</h4>
                            <p data-i18n="system.roles.table.no_users_description">No users are registered in the system yet.</p>
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
                            <i class="fa fa-sitemap"></i> <span data-i18n="system.roles.section.hierarchy">Role Hierarchy & Descriptions</span>
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body" style="display: none;">
                        <div class="alert alert-info">
                            <h4><i class="fa fa-info-circle"></i> <span data-i18n="system.roles.section.hierarchy_title">Understanding Role Hierarchy</span></h4>
                            <p data-i18n="system.roles.section.hierarchy_description">Roles define what users can access and do within the system. Each role has specific permissions assigned.</p>
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
                                        <li><strong data-i18n="system.roles.role_card.users_label">Users:</strong> {{ $role->users_count }}</li>
                                        <li><strong data-i18n="system.roles.role_card.permissions_label">Permissions:</strong> {{ $role->permissions_count }}</li>
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
                            <i class="fa fa-bolt"></i> <span data-i18n="system.roles.section.quick_actions">Quick Actions</span>
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="btn-group btn-group-justified" role="group">
                            <a href="{{ route('system.permissions') }}" class="btn btn-app bg-light-blue">
                                <i class="fa fa-key"></i> <span data-i18n="system.roles.action.permissions">Permissions</span>
                            </a>
                            <a href="{{ route('users.index') }}" class="btn btn-app bg-green">
                                <i class="fa fa-users"></i> <span data-i18n="system.roles.action.users">Users</span>
                            </a>
                            <a href="{{ route('system.settings') }}" class="btn btn-app bg-orange">
                                <i class="fa fa-cogs"></i> <span data-i18n="system.roles.action.settings">Settings</span>
                            </a>
                            <button type="button" class="btn btn-app bg-yellow" onclick="clearCache()">
                                <i class="fa fa-refresh"></i> <span data-i18n="system.roles.action.clear_cache">Clear Cache</span>
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
                        <i class="fa fa-plus-circle"></i> <span data-i18n="system.roles.modal.create.title">Create New Role</span>
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> <strong data-i18n="system.roles.modal.note_label">Note:</strong> <span data-i18n="system.roles.modal.create.note">Only Super Administrators can create new roles.</span>
                    </div>

                    <div class="form-group">
                        <label for="create_name"><span data-i18n="system.roles.modal.role_name">Role Name</span> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="create_name" name="name" 
                               placeholder="e.g., developer, accountant, hr-manager" data-i18n-placeholder="system.roles.modal.role_name_placeholder" required 
                               pattern="[a-zA-Z0-9\-_]+" 
                               data-i18n-title="system.roles.modal.role_name_title" title="Only letters, numbers, dashes and underscores allowed">
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i> <span data-i18n="system.roles.modal.role_name_help">Use lowercase letters, numbers, dashes and underscores only. No spaces.</span>
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="create_display_name"><span data-i18n="system.roles.modal.display_name">Display Name</span> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="create_display_name" name="display_name" 
                               placeholder="e.g., Developer, Accountant, HR Manager" data-i18n-placeholder="system.roles.modal.display_name_placeholder" required maxlength="255">
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i> <span data-i18n="system.roles.modal.display_name_help">Human-readable name shown in the interface.</span>
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="create_description" data-i18n="system.roles.modal.description">Description</label>
                        <textarea class="form-control" id="create_description" name="description" 
                                  rows="3" maxlength="500" 
                                  placeholder="Describe what this role is for and what responsibilities it has..." data-i18n-placeholder="system.roles.modal.description_placeholder"></textarea>
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i> <span data-i18n="system.roles.modal.description_help">Optional. Maximum 500 characters.</span>
                        </small>
                    </div>

                    <div class="form-group">
                        <label data-i18n="system.roles.modal.permissions">Permissions</label>
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
                            <i class="fa fa-info-circle"></i> <span data-i18n="system.roles.modal.permissions_help">Select permissions to assign to this role. You can change this later.</span>
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> <span data-i18n="system.roles.action.cancel">Cancel</span>
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> <span data-i18n="system.roles.action.create_role">Create Role</span>
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
                        <i class="fa fa-edit"></i> <span data-i18n="system.roles.modal.edit.title">Edit Role</span>
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i> <strong data-i18n="system.roles.modal.warning_label">Warning:</strong> <span data-i18n="system.roles.modal.edit.warning">Changing role permissions will affect all users with this role.</span>
                    </div>

                    <input type="hidden" id="edit_role_id" name="role_id">

                    <div class="form-group">
                        <label for="edit_name"><span data-i18n="system.roles.modal.role_name">Role Name</span> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" 
                               placeholder="e.g., developer, accountant, hr-manager" data-i18n-placeholder="system.roles.modal.role_name_placeholder" required 
                               pattern="[a-zA-Z0-9\-_]+" 
                               data-i18n-title="system.roles.modal.role_name_title" title="Only letters, numbers, dashes and underscores allowed">
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i> <span data-i18n="system.roles.modal.role_name_help">Use lowercase letters, numbers, dashes and underscores only. No spaces.</span>
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="edit_display_name"><span data-i18n="system.roles.modal.display_name">Display Name</span> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_display_name" name="display_name" 
                               placeholder="e.g., Developer, Accountant, HR Manager" data-i18n-placeholder="system.roles.modal.display_name_placeholder" required maxlength="255">
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i> <span data-i18n="system.roles.modal.display_name_help">Human-readable name shown in the interface.</span>
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="edit_description" data-i18n="system.roles.modal.description">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" 
                                  rows="3" maxlength="500" 
                                  placeholder="Describe what this role is for and what responsibilities it has..." data-i18n-placeholder="system.roles.modal.description_placeholder"></textarea>
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i> <span data-i18n="system.roles.modal.description_help">Optional. Maximum 500 characters.</span>
                        </small>
                    </div>

                    <div class="form-group">
                        <label data-i18n="system.roles.modal.permissions">Permissions</label>
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
                            <i class="fa fa-info-circle"></i> <span data-i18n="system.roles.modal.permissions_edit_help">Select permissions to assign to this role.</span>
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> <span data-i18n="system.roles.action.cancel">Cancel</span>
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fa fa-save"></i> <span data-i18n="system.roles.action.update_role">Update Role</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var systemRolesUsersTable = null;

(function() {
    var translations = {
        en: {
            'system.roles.stats.total_roles': 'Total Roles',
            'system.roles.stats.view_details': 'View Details',
            'system.roles.stats.total_permissions': 'Total Permissions',
            'system.roles.stats.manage': 'Manage',
            'system.roles.stats.active_users': 'Active Users',
            'system.roles.stats.view_users': 'View Users',
            'system.roles.stats.unassigned_users': 'Unassigned Users',
            'system.roles.stats.assign_roles': 'Assign Roles',
            'system.roles.section.system_roles': 'System Roles',
            'system.roles.section.system_roles_help': 'Overview of all system roles with their assigned users and permissions.',
            'system.roles.section.permissions_matrix': 'Role Permissions Matrix',
            'system.roles.section.permissions_matrix_help': "Each role's assigned permissions. Click to view full details.",
            'system.roles.section.user_assignments': 'User Role Assignments',
            'system.roles.section.user_assignments_help': 'All users with their assigned roles.',
            'system.roles.section.users_without_roles': 'users without roles!',
            'system.roles.section.hierarchy': 'Role Hierarchy & Descriptions',
            'system.roles.section.hierarchy_title': 'Understanding Role Hierarchy',
            'system.roles.section.hierarchy_description': 'Roles define what users can access and do within the system. Each role has specific permissions assigned.',
            'system.roles.section.quick_actions': 'Quick Actions',
            'system.roles.role_card.users_suffix': 'users',
            'system.roles.role_card.permissions_suffix': 'permissions',
            'system.roles.role_card.no_permissions': 'No permissions assigned to this role',
            'system.roles.role_card.users_label': 'Users:',
            'system.roles.role_card.permissions_label': 'Permissions:',
            'system.roles.table.user': 'User',
            'system.roles.table.roles': 'Roles',
            'system.roles.table.actions': 'Actions',
            'system.roles.table.no_role': 'No Role',
            'system.roles.table.no_users_title': 'No Users Found',
            'system.roles.table.no_users_description': 'No users are registered in the system yet.',
            'system.roles.action.create_new_role': 'Create New Role',
            'system.roles.action.assign_permissions': 'Assign Permissions',
            'system.roles.action.permissions': 'Permissions',
            'system.roles.action.users': 'Users',
            'system.roles.action.settings': 'Settings',
            'system.roles.action.clear_cache': 'Clear Cache',
            'system.roles.action.cancel': 'Cancel',
            'system.roles.action.create_role': 'Create Role',
            'system.roles.action.update_role': 'Update Role',
            'system.roles.action.edit_role_title': 'Edit Role',
            'system.roles.action.delete_role_title': 'Delete Role',
            'system.roles.action.edit_user_title': 'Edit User',
            'system.roles.modal.create.title': 'Create New Role',
            'system.roles.modal.edit.title': 'Edit Role',
            'system.roles.modal.note_label': 'Note:',
            'system.roles.modal.warning_label': 'Warning:',
            'system.roles.modal.create.note': 'Only Super Administrators can create new roles.',
            'system.roles.modal.edit.warning': 'Changing role permissions will affect all users with this role.',
            'system.roles.modal.role_name': 'Role Name',
            'system.roles.modal.role_name_placeholder': 'e.g., developer, accountant, hr-manager',
            'system.roles.modal.role_name_title': 'Only letters, numbers, dashes and underscores allowed',
            'system.roles.modal.role_name_help': 'Use lowercase letters, numbers, dashes and underscores only. No spaces.',
            'system.roles.modal.display_name': 'Display Name',
            'system.roles.modal.display_name_placeholder': 'e.g., Developer, Accountant, HR Manager',
            'system.roles.modal.display_name_help': 'Human-readable name shown in the interface.',
            'system.roles.modal.description': 'Description',
            'system.roles.modal.description_placeholder': 'Describe what this role is for and what responsibilities it has...',
            'system.roles.modal.description_help': 'Optional. Maximum 500 characters.',
            'system.roles.modal.permissions': 'Permissions',
            'system.roles.modal.permissions_help': 'Select permissions to assign to this role. You can change this later.',
            'system.roles.modal.permissions_edit_help': 'Select permissions to assign to this role.',
            'system.roles.runtime.edit_error': 'Error loading role data: ',
            'system.roles.runtime.delete_confirm_prefix': 'Are you sure you want to delete the role',
            'system.roles.runtime.delete_confirm_suffix': 'This action cannot be undone.',
            'system.roles.runtime.delete_impact_users': 'Users with this role will need to be assigned a different role.',
            'system.roles.runtime.delete_impact_system': 'System roles (super-admin, admin, management, user) cannot be deleted.',
            'system.roles.runtime.clear_cache_confirm': 'Are you sure you want to clear all caches?',
            'system.roles.runtime.clear_cache_slowdown': 'This may temporarily slow down the application.',
            'system.roles.runtime.success_prefix': 'Success: ',
            'system.roles.runtime.error_prefix': 'Error: ',
            'system.roles.datatable.search': 'Search:',
            'system.roles.datatable.search_placeholder': 'Search users...',
            'system.roles.datatable.length_menu': 'Show _MENU_',
            'system.roles.datatable.info': '_START_-_END_ of _TOTAL_',
            'system.roles.datatable.info_empty': 'No users',
            'system.roles.datatable.info_filtered': '(filtered from _MAX_)',
            'system.roles.datatable.empty_table': 'No users found',
            'system.roles.datatable.zero_records': 'No matching users'
        },
        id: {
            'system.roles.stats.total_roles': 'Total Peran',
            'system.roles.stats.view_details': 'Lihat Detail',
            'system.roles.stats.total_permissions': 'Total Izin',
            'system.roles.stats.manage': 'Kelola',
            'system.roles.stats.active_users': 'Pengguna Aktif',
            'system.roles.stats.view_users': 'Lihat Pengguna',
            'system.roles.stats.unassigned_users': 'Pengguna Tanpa Peran',
            'system.roles.stats.assign_roles': 'Tetapkan Peran',
            'system.roles.section.system_roles': 'Peran Sistem',
            'system.roles.section.system_roles_help': 'Ringkasan seluruh peran sistem beserta pengguna dan izin yang dimiliki.',
            'system.roles.section.permissions_matrix': 'Matriks Izin Peran',
            'system.roles.section.permissions_matrix_help': 'Setiap peran dan izin yang dimiliki. Klik untuk melihat detail lengkap.',
            'system.roles.section.user_assignments': 'Penugasan Peran Pengguna',
            'system.roles.section.user_assignments_help': 'Semua pengguna beserta peran yang ditetapkan.',
            'system.roles.section.users_without_roles': 'pengguna tanpa peran!',
            'system.roles.section.hierarchy': 'Hierarki Peran & Deskripsi',
            'system.roles.section.hierarchy_title': 'Memahami Hierarki Peran',
            'system.roles.section.hierarchy_description': 'Peran menentukan akses dan tindakan pengguna dalam sistem. Setiap peran memiliki izin spesifik.',
            'system.roles.section.quick_actions': 'Aksi Cepat',
            'system.roles.role_card.users_suffix': 'pengguna',
            'system.roles.role_card.permissions_suffix': 'izin',
            'system.roles.role_card.no_permissions': 'Tidak ada izin untuk peran ini',
            'system.roles.role_card.users_label': 'Pengguna:',
            'system.roles.role_card.permissions_label': 'Izin:',
            'system.roles.table.user': 'Pengguna',
            'system.roles.table.roles': 'Peran',
            'system.roles.table.actions': 'Aksi',
            'system.roles.table.no_role': 'Tanpa Peran',
            'system.roles.table.no_users_title': 'Tidak Ada Pengguna',
            'system.roles.table.no_users_description': 'Belum ada pengguna yang terdaftar dalam sistem.',
            'system.roles.action.create_new_role': 'Buat Peran Baru',
            'system.roles.action.assign_permissions': 'Tetapkan Izin',
            'system.roles.action.permissions': 'Izin',
            'system.roles.action.users': 'Pengguna',
            'system.roles.action.settings': 'Pengaturan',
            'system.roles.action.clear_cache': 'Bersihkan Cache',
            'system.roles.action.cancel': 'Batal',
            'system.roles.action.create_role': 'Buat Peran',
            'system.roles.action.update_role': 'Perbarui Peran',
            'system.roles.action.edit_role_title': 'Ubah Peran',
            'system.roles.action.delete_role_title': 'Hapus Peran',
            'system.roles.action.edit_user_title': 'Ubah Pengguna',
            'system.roles.modal.create.title': 'Buat Peran Baru',
            'system.roles.modal.edit.title': 'Ubah Peran',
            'system.roles.modal.note_label': 'Catatan:',
            'system.roles.modal.warning_label': 'Peringatan:',
            'system.roles.modal.create.note': 'Hanya Super Administrator yang dapat membuat peran baru.',
            'system.roles.modal.edit.warning': 'Perubahan izin peran akan memengaruhi semua pengguna dengan peran ini.',
            'system.roles.modal.role_name': 'Nama Peran',
            'system.roles.modal.role_name_placeholder': 'contoh: developer, accountant, hr-manager',
            'system.roles.modal.role_name_title': 'Hanya huruf, angka, tanda minus, dan underscore yang diperbolehkan',
            'system.roles.modal.role_name_help': 'Gunakan huruf kecil, angka, tanda minus, dan underscore saja. Tanpa spasi.',
            'system.roles.modal.display_name': 'Nama Tampilan',
            'system.roles.modal.display_name_placeholder': 'contoh: Developer, Accountant, HR Manager',
            'system.roles.modal.display_name_help': 'Nama yang ditampilkan pada antarmuka.',
            'system.roles.modal.description': 'Deskripsi',
            'system.roles.modal.description_placeholder': 'Jelaskan tujuan peran ini dan tanggung jawabnya...',
            'system.roles.modal.description_help': 'Opsional. Maksimum 500 karakter.',
            'system.roles.modal.permissions': 'Izin',
            'system.roles.modal.permissions_help': 'Pilih izin yang ingin diberikan ke peran ini. Bisa diubah nanti.',
            'system.roles.modal.permissions_edit_help': 'Pilih izin yang ingin diberikan ke peran ini.',
            'system.roles.runtime.edit_error': 'Gagal memuat data peran: ',
            'system.roles.runtime.delete_confirm_prefix': 'Apakah Anda yakin ingin menghapus peran',
            'system.roles.runtime.delete_confirm_suffix': 'Aksi ini tidak dapat dibatalkan.',
            'system.roles.runtime.delete_impact_users': 'Pengguna dengan peran ini harus diberi peran lain.',
            'system.roles.runtime.delete_impact_system': 'Peran sistem (super-admin, admin, management, user) tidak dapat dihapus.',
            'system.roles.runtime.clear_cache_confirm': 'Apakah Anda yakin ingin membersihkan semua cache?',
            'system.roles.runtime.clear_cache_slowdown': 'Ini mungkin memperlambat aplikasi sementara.',
            'system.roles.runtime.success_prefix': 'Berhasil: ',
            'system.roles.runtime.error_prefix': 'Kesalahan: ',
            'system.roles.datatable.search': 'Cari:',
            'system.roles.datatable.search_placeholder': 'Cari pengguna...',
            'system.roles.datatable.length_menu': 'Tampilkan _MENU_',
            'system.roles.datatable.info': '_START_-_END_ dari _TOTAL_',
            'system.roles.datatable.info_empty': 'Tidak ada pengguna',
            'system.roles.datatable.info_filtered': '(difilter dari _MAX_)',
            'system.roles.datatable.empty_table': 'Tidak ada pengguna',
            'system.roles.datatable.zero_records': 'Tidak ada pengguna yang cocok'
        }
    };

    var currentLanguage = 'en';
    var userId = '{{ (int) auth()->id() }}';
    var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
    var englishButton = document.getElementById('systemRolesLanguageEnglish');
    var indonesianButton = document.getElementById('systemRolesLanguageIndonesian');

    function getLanguage() {
        try {
            var raw = window.localStorage.getItem(languageStorageKey);
            if (!raw) {
                return 'en';
            }

            var parsed = JSON.parse(raw);
            return parsed && parsed.language === 'id' ? 'id' : 'en';
        } catch (error) {
            return 'en';
        }
    }

    function saveLanguage(language) {
        try {
            var raw = window.localStorage.getItem(languageStorageKey);
            var parsed = raw ? JSON.parse(raw) : {};
            parsed.language = language === 'id' ? 'id' : 'en';
            window.localStorage.setItem(languageStorageKey, JSON.stringify(parsed));
        } catch (error) {
            // Keep silent if localStorage is unavailable.
        }
    }

    function getLabel(key, fallback) {
        var dictionary = translations[currentLanguage] || translations.en;
        return dictionary[key] || fallback || key;
    }

    function applyLanguage(language) {
        currentLanguage = language === 'id' ? 'id' : 'en';
        var dictionary = translations[currentLanguage] || translations.en;

        Array.prototype.forEach.call(document.querySelectorAll('[data-i18n]'), function(node) {
            var key = node.getAttribute('data-i18n');
            if (dictionary[key]) {
                node.textContent = dictionary[key];
            }
        });

        Array.prototype.forEach.call(document.querySelectorAll('[data-i18n-placeholder]'), function(node) {
            var key = node.getAttribute('data-i18n-placeholder');
            if (dictionary[key]) {
                node.setAttribute('placeholder', dictionary[key]);
            }
        });

        Array.prototype.forEach.call(document.querySelectorAll('[data-i18n-title]'), function(node) {
            var key = node.getAttribute('data-i18n-title');
            if (dictionary[key]) {
                node.setAttribute('title', dictionary[key]);
            }
        });

        if (englishButton && indonesianButton) {
            englishButton.classList.toggle('active', currentLanguage === 'en');
            indonesianButton.classList.toggle('active', currentLanguage === 'id');
        }

        if (typeof window.systemRolesRefreshRuntimeText === 'function') {
            window.systemRolesRefreshRuntimeText();
        }
    }

    window.systemRolesLabel = getLabel;
    window.systemRolesDataTableLanguage = function() {
        return {
            search: getLabel('system.roles.datatable.search', 'Search:'),
            searchPlaceholder: getLabel('system.roles.datatable.search_placeholder', 'Search users...'),
            lengthMenu: getLabel('system.roles.datatable.length_menu', 'Show _MENU_'),
            info: getLabel('system.roles.datatable.info', '_START_-_END_ of _TOTAL_'),
            infoEmpty: getLabel('system.roles.datatable.info_empty', 'No users'),
            infoFiltered: getLabel('system.roles.datatable.info_filtered', '(filtered from _MAX_)'),
            emptyTable: getLabel('system.roles.datatable.empty_table', 'No users found'),
            zeroRecords: getLabel('system.roles.datatable.zero_records', 'No matching users')
        };
    };

    if (englishButton && indonesianButton) {
        englishButton.addEventListener('click', function() {
            saveLanguage('en');
            applyLanguage('en');
        });

        indonesianButton.addEventListener('click', function() {
            saveLanguage('id');
            applyLanguage('id');
        });
    }

    applyLanguage(getLanguage());
})();

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
        alert(window.systemRolesLabel('system.roles.runtime.edit_error', 'Error loading role data: ') + error.message);
    });
}

// Delete Role Function
function deleteRole(roleId, roleName) {
    if (confirm(
        window.systemRolesLabel('system.roles.runtime.delete_confirm_prefix', 'Are you sure you want to delete the role') + ' "' + roleName + '"?\n\n' +
        window.systemRolesLabel('system.roles.runtime.delete_confirm_suffix', 'This action cannot be undone.') + '\n\n- ' +
        window.systemRolesLabel('system.roles.runtime.delete_impact_users', 'Users with this role will need to be assigned a different role.') + '\n- ' +
        window.systemRolesLabel('system.roles.runtime.delete_impact_system', 'System roles (super-admin, admin, management, user) cannot be deleted.')
    )) {
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
    if (confirm(
        window.systemRolesLabel('system.roles.runtime.clear_cache_confirm', 'Are you sure you want to clear all caches?') + ' ' +
        window.systemRolesLabel('system.roles.runtime.clear_cache_slowdown', 'This may temporarily slow down the application.')
    )) {
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
                alert(window.systemRolesLabel('system.roles.runtime.success_prefix', 'Success: ') + data.message);
                location.reload();
            } else {
                alert(window.systemRolesLabel('system.roles.runtime.error_prefix', 'Error: ') + data.message);
            }
        })
        .catch(error => {
            alert(window.systemRolesLabel('system.roles.runtime.error_prefix', 'Error: ') + error.message);
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
            
            systemRolesUsersTable = $('#users-table').DataTable({
                "responsive": false,
                "pageLength": useOptimizedConfig ? 50 : 25,
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "order": [[0, "asc"]],
                "deferRender": true,
                "processing": false,
                "autoWidth": false,
                "stateSave": false,
                "search": { smart: false },
                "language": window.systemRolesDataTableLanguage(),
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

            window.systemRolesRefreshRuntimeText = function() {
                if (!systemRolesUsersTable || !systemRolesUsersTable.settings || !systemRolesUsersTable.settings()[0]) {
                    return;
                }

                systemRolesUsersTable.settings()[0].oLanguage = window.systemRolesDataTableLanguage();
                systemRolesUsersTable.draw(false);
            };

            window.systemRolesRefreshRuntimeText();
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
