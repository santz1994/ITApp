@extends('layouts.app')

@section('main-content')
<div class="container-fluid">
    <!-- Page Title -->
    <div class="row">
        <div class="col-md-12">
            <h1 class="page-header">
                <i class="fa fa-shield"></i> Menu Permissions
                <small>{{ $menu->label }}</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="{{ route('admin.menus.index') }}"><i class="fa fa-bars"></i> Menu Management</a></li>
                <li class="active">Permissions</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="icon fa fa-check"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="icon fa fa-ban"></i> {{ session('error') }}
    </div>
    @endif

    <div class="row">
        <!-- Menu Info -->
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Menu Details</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Label:</th>
                            <td><strong>{{ $menu->label }}</strong></td>
                        </tr>
                        <tr>
                            <th>Icon:</th>
                            <td><i class="fa {{ $menu->icon }}"></i> {{ $menu->icon }}</td>
                        </tr>
                        <tr>
                            <th>Route:</th>
                            <td><code>{{ $menu->route ?: 'N/A' }}</code></td>
                        </tr>
                        <tr>
                            <th>URL:</th>
                            <td>{{ $menu->url ?: 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Parent:</th>
                            <td>{{ $menu->parent ? $menu->parent->label : 'Top Level' }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="label label-{{ $menu->is_active ? 'success' : 'warning' }}">
                                    {{ $menu->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>{{ $menu->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="box-footer">
                    <a href="{{ route('admin.menus.edit', $menu->id) }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-edit"></i> Edit Menu
                    </a>
                    <a href="{{ route('admin.menus.index') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            <!-- Current Permissions Summary -->
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-users"></i> Current Access</h3>
                </div>
                <div class="box-body">
                    @if($menu->roles->count() > 0)
                    <p>This menu is accessible to:</p>
                    <ul class="list-unstyled">
                        @foreach($menu->roles as $role)
                        <li>
                            <span class="label label-info">
                                <i class="fa fa-user"></i> {{ $role->name }}
                            </span>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-muted">
                        <i class="fa fa-exclamation-triangle"></i> No roles assigned yet. 
                        This menu is not visible to any users.
                    </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Permission Management Form -->
        <div class="col-md-8">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-lock"></i> Assign Role Permissions</h3>
                    <div class="box-tools pull-right">
                        <span class="badge bg-yellow">{{ $allRoles->count() }} roles available</span>
                    </div>
                </div>
                <form action="{{ route('admin.menus.permissions.update', $menu->id) }}" method="POST">
                    @csrf
                    <div class="box-body">
                        <p class="text-info">
                            <i class="fa fa-info-circle"></i> 
                            Select which roles can see this menu item. Users with selected roles will have access.
                        </p>

                        <div class="row">
                            @foreach($allRoles->chunk(ceil($allRoles->count() / 2)) as $rolesChunk)
                            <div class="col-md-6">
                                @foreach($rolesChunk as $role)
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" 
                                               name="role_ids[]" 
                                               value="{{ $role->id }}"
                                               {{ $menu->roles->contains($role->id) ? 'checked' : '' }}>
                                        <strong>{{ ucfirst($role->name) }}</strong>
                                        @if($role->users_count > 0)
                                        <span class="badge bg-blue">{{ $role->users_count }} users</span>
                                        @endif
                                    </label>
                                    <p class="help-block" style="margin-left: 20px;">
                                        @if($role->name == 'super-admin')
                                            <small class="text-muted">Full system access</small>
                                        @elseif($role->name == 'admin')
                                            <small class="text-muted">Administrative access</small>
                                        @elseif($role->name == 'management')
                                            <small class="text-muted">Management level access</small>
                                        @elseif($role->name == 'user')
                                            <small class="text-muted">Standard user access</small>
                                        @else
                                            <small class="text-muted">{{ $role->guard_name }}</small>
                                        @endif
                                    </p>
                                </div>
                                @endforeach
                            </div>
                            @endforeach
                        </div>

                        @if($allRoles->count() == 0)
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i> 
                            No roles found. Please create roles first in System Management.
                        </div>
                        @endif
                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-warning" {{ $allRoles->count() == 0 ? 'disabled' : '' }}>
                            <i class="fa fa-save"></i> Update Permissions
                        </button>
                        <button type="button" class="btn btn-default" onclick="uncheckAll()">
                            <i class="fa fa-times"></i> Clear All
                        </button>
                        <button type="button" class="btn btn-success" onclick="checkAll()">
                            <i class="fa fa-check"></i> Select All
                        </button>
                    </div>
                </form>
            </div>

            <!-- Permission Matrix (if available) -->
            @if(isset($permissionMatrix) && count($permissionMatrix) > 0)
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-table"></i> Permission Matrix</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Can View</th>
                                <th>Assigned On</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permissionMatrix as $item)
                            <tr>
                                <td><strong>{{ $item['role_name'] }}</strong></td>
                                <td>
                                    @if($item['can_view'])
                                    <span class="label label-success"><i class="fa fa-check"></i> Yes</span>
                                    @else
                                    <span class="label label-danger"><i class="fa fa-times"></i> No</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item['assigned_at'])
                                    {{ \Carbon\Carbon::parse($item['assigned_at'])->format('Y-m-d H:i') }}
                                    @else
                                    <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Help Section -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default collapsed-box">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-question-circle"></i> Help & Guidelines</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>How Permissions Work</h4>
                            <ol>
                                <li>Select the roles that should have access to this menu item</li>
                                <li>Users with selected roles will see the menu in the sidebar</li>
                                <li>Menu visibility is cached for performance - changes take effect immediately after cache clear</li>
                            </ol>
                        </div>
                        <div class="col-md-6">
                            <h4>Best Practices</h4>
                            <ul>
                                <li><strong>Super Admin:</strong> Always has access to all menus</li>
                                <li><strong>Admin:</strong> Should have access to most management features</li>
                                <li><strong>Management:</strong> Limited to reporting and monitoring</li>
                                <li><strong>User:</strong> Basic features only</li>
                            </ul>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 15px;">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="fa fa-lightbulb-o"></i> 
                                <strong>Tip:</strong> If users can't see a menu after assignment, try clearing the cache from System Settings.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function checkAll() {
    $('input[name="role_ids[]"]').prop('checked', true);
}

function uncheckAll() {
    $('input[name="role_ids[]"]').prop('checked', false);
}

$(document).ready(function() {
    // Show confirmation before updating
    $('form').on('submit', function(e) {
        var checkedCount = $('input[name="role_ids[]"]:checked').length;
        
        if (checkedCount === 0) {
            if (!confirm('No roles selected. This menu will not be visible to any users. Continue?')) {
                e.preventDefault();
                return false;
            }
        }
    });
});
</script>
@endpush
@endsection
