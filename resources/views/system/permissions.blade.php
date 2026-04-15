@extends('layouts.app')

@section('main-content')

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Permissions Management',
    'subtitle' => 'Manage system permissions and role assignments',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'System', 'url' => route('system.settings')],
        ['label' => 'Permissions']
    ],
    'actions' => '
        <a href="'.route('system.settings').'" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> <span class="hidden-xs">Back</span>
        </a>
    '
])

<div class="container-fluid">
    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="fa fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Quick Stats --}}
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{ count($permissions ?? []) }}</h3>
                    <p>Total Permissions</p>
                </div>
                <div class="icon">
                    <i class="fa fa-key"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ isset($roles) ? count($roles) : 0 }}</h3>
                    <p>System Roles</p>
                </div>
                <div class="icon">
                    <i class="fa fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ isset($permissions) ? $permissions->filter(fn($p) => $p->roles->count() > 0)->count() : 0 }}</h3>
                    <p>Assigned Permissions</p>
                </div>
                <div class="icon">
                    <i class="fa fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{ isset($permissions) ? $permissions->filter(fn($p) => $p->roles->count() === 0)->count() : 0 }}</h3>
                    <p>Unassigned</p>
                </div>
                <div class="icon">
                    <i class="fa fa-exclamation-circle"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <!-- Permissions List -->
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-key"></i> System Permissions
                        <span class="badge bg-blue">{{ count($permissions ?? []) }}</span>
                    </h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    @if(isset($permissions) && count($permissions) > 0)
                    <div class="table-responsive">
                        <table id="permissions-table" class="table table-enhanced table-striped table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 40px;"><i class="fa fa-hashtag"></i></th>
                                    <th><i class="fa fa-key"></i> Permission Name</th>
                                    <th style="width: 80px;"><i class="fa fa-shield"></i> Guard</th>
                                    <th><i class="fa fa-users"></i> Assigned Roles</th>
                                    <th style="width: 110px;"><i class="fa fa-calendar"></i> Created</th>
                                    <th style="width: 140px;"><i class="fa fa-cogs"></i> Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permissions as $permission)
                                <tr>
                                    <td class="text-center"><strong>{{ $permission->id }}</strong></td>
                                    <td>
                                        <strong>{{ $permission->name }}</strong>
                                        @if($permission->roles->count() === 0)
                                        <br><small class="text-danger"><i class="fa fa-exclamation-triangle"></i> Not assigned</small>
                                        @endif
                                    </td>
                                    <td><span class="label label-default">{{ $permission->guard_name }}</span></td>
                                    <td>
                                        @if($permission->roles->count() > 0)
                                            @foreach($permission->roles as $role)
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
                                            <span class="text-muted"><i class="fa fa-ban"></i> None</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $permission->created_at->format('M d, Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-xs">
                                            <button class="btn btn-primary" onclick="editPermission({{ $permission->id }})" title="Edit Permission">
                                                <i class="fa fa-edit"></i> Edit
                                            </button>
                                            @if($permission->roles->count() === 0)
                                            <button class="btn btn-danger" onclick="deletePermission({{ $permission->id }})" title="Delete Permission">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                            @else
                                            <button class="btn btn-default disabled" title="Cannot delete - assigned to roles" disabled>
                                                <i class="fa fa-lock"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="empty-state text-center" style="padding: 40px;">
                        <i class="fa fa-key fa-3x text-muted" style="opacity: 0.3;"></i>
                        <h4>No Permissions Found</h4>
                        <p class="text-muted">Create your first permission using the form on the right.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Permission Actions -->
        <div class="col-md-4">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-plus-circle"></i> Create Permission
                    </h3>
                </div>
                <div class="box-body">
                    <form method="POST" action="{{ route('system.permissions.create') }}" id="create-permission-form">
                        @csrf
                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label for="permission_name">
                                Permission Name <span class="text-red">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="permission_name" 
                                   name="name" 
                                   placeholder="e.g., view-reports" 
                                   value="{{ old('name') }}"
                                   required>
                            @if($errors->has('name'))
                                <span class="help-block">{{ $errors->first('name') }}</span>
                            @endif
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i> Use lowercase with hyphens (e.g., manage-users)
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="guard_name">Guard</label>
                            <select class="form-control" id="guard_name" name="guard_name">
                                <option value="web" selected>Web</option>
                                <option value="api">API</option>
                            </select>
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i> Use 'web' for standard permissions
                            </small>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fa fa-plus-circle"></i> Create Permission
                        </button>
                    </form>
                </div>
            </div>

            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-link"></i> Assign to Role
                    </h3>
                </div>
                <div class="box-body">
                    <form method="POST" action="{{ route('system.permissions.assign') }}" id="assign-permission-form">
                        @csrf
                        <div class="form-group {{ $errors->has('permission_id') ? 'has-error' : '' }}">
                            <label for="permission_id">
                                Permission <span class="text-red">*</span>
                            </label>
                            <select class="form-control select2" id="permission_id" name="permission_id" required>
                                <option value="">-- Select Permission --</option>
                                @if(isset($permissions))
                                    @foreach($permissions as $permission)
                                    <option value="{{ $permission->id }}" {{ old('permission_id') == $permission->id ? 'selected' : '' }}>
                                        {{ $permission->name }}
                                    </option>
                                    @endforeach
                                @endif
                            </select>
                            @if($errors->has('permission_id'))
                                <span class="help-block">{{ $errors->first('permission_id') }}</span>
                            @endif
                        </div>
                        
                        <div class="form-group {{ $errors->has('role_id') ? 'has-error' : '' }}">
                            <label for="role_id">
                                Role <span class="text-red">*</span>
                            </label>
                            <select class="form-control select2" id="role_id" name="role_id" required>
                                <option value="">-- Select Role --</option>
                                @if(isset($roles))
                                    @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->display_name ?? ucfirst(str_replace('-', ' ', $role->name)) }}
                                    </option>
                                    @endforeach
                                @endif
                            </select>
                            @if($errors->has('role_id'))
                                <span class="help-block">{{ $errors->first('role_id') }}</span>
                            @endif
                        </div>
                        
                        <button type="submit" class="btn btn-warning btn-block">
                            <i class="fa fa-link"></i> Assign Permission to Role
                        </button>
                    </form>
                </div>
            </div>

            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-info-circle"></i> Quick Stats
                    </h3>
                </div>
                <div class="box-body">
                    <div class="info-box bg-aqua" style="margin-bottom: 10px;">
                        <span class="info-box-icon"><i class="fa fa-key"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Permissions</span>
                            <span class="info-box-number">{{ isset($permissions) ? count($permissions) : 0 }}</span>
                        </div>
                    </div>
                    
                    <div class="info-box bg-green" style="margin-bottom: 10px;">
                        <span class="info-box-icon"><i class="fa fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">System Roles</span>
                            <span class="info-box-number">{{ isset($roles) ? count($roles) : 0 }}</span>
                        </div>
                    </div>
                    
                    <div class="info-box bg-red" style="margin-bottom: 0;">
                        <span class="info-box-icon"><i class="fa fa-exclamation-triangle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Unassigned</span>
                            <span class="info-box-number">
                                {{ isset($permissions) ? $permissions->filter(function($p) { return $p->roles->count() === 0; })->count() : 0 }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Role-Permission Matrix -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-table"></i> Permission Matrix
                    </h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    @if(isset($roles) && isset($permissions) && count($roles) > 0 && count($permissions) > 0)
                    <p class="text-muted">
                        <i class="fa fa-info-circle"></i> This matrix shows which permissions are assigned to each role.
                        <span class="text-success"><i class="fa fa-check"></i> = Assigned</span> | 
                        <span class="text-danger"><i class="fa fa-times"></i> = Not assigned</span>
                    </p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-condensed table-hover">
                            <thead class="bg-light-blue">
                                <tr>
                                    <th style="width: 250px;"><i class="fa fa-key"></i> Permission</th>
                                    @foreach($roles as $role)
                                    <th class="text-center" style="min-width: 100px;">
                                        <span class="label label-{{ $role->name === 'super-admin' ? 'danger' : ($role->name === 'admin' ? 'warning' : ($role->name === 'management' ? 'info' : 'success')) }}">
                                            {{ $role->display_name ?? ucfirst(str_replace('-', ' ', $role->name)) }}
                                        </span>
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permissions as $permission)
                                <tr>
                                    <td><strong>{{ $permission->name }}</strong></td>
                                    @foreach($roles as $role)
                                    <td class="text-center" style="background-color: {{ $role->hasPermissionTo($permission->name) ? '#dff0d8' : '#f2dede' }};">
                                        @if($role->hasPermissionTo($permission->name))
                                            <i class="fa fa-check-circle fa-lg text-success"></i>
                                        @else
                                            <i class="fa fa-times-circle fa-lg text-danger"></i>
                                        @endif
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                            </table>
                        </div>
                        @else
                        <p class="text-muted">No data available for permission matrix.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Edit Permission Modal -->
    <div class="modal fade" id="editPermissionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">
                        <i class="fa fa-edit"></i> Edit Permission
                    </h4>
                </div>
                <form id="editPermissionForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_permission_id" name="permission_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_permission_name">Permission Name</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="edit_permission_name" 
                                   name="name" 
                                   placeholder="e.g., view-reports" 
                                   required>
                            <small class="help-block text-muted">
                                Use lowercase with hyphens (e.g., view-assets, manage-users)
                            </small>
                        </div>
                        <div class="form-group">
                            <label for="edit_guard_name">Guard</label>
                            <select class="form-control" id="edit_guard_name" name="guard_name" required>
                                <option value="web">Web</option>
                                <option value="api">API</option>
                            </select>
                        </div>
                        
                        <hr>
                        
                        <div class="form-group">
                            <label>
                                <i class="fa fa-users"></i> Assign to Roles
                            </label>
                            <p class="help-block">Select which roles should have this permission</p>
                            <div id="edit_permission_role_checkboxes">
                                <!-- Role checkboxes will be dynamically loaded here -->
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <i class="fa fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Setup CSRF token for all AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Available roles data (from backend)
var availableRoles = @json($roles ?? []);

// Edit Permission Function
function editPermission(permissionId) {
    // Fetch permission details
    $.ajax({
        url: '/system/permissions/' + permissionId,
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                var permission = response.permission;
                
                // Populate modal fields
                $('#edit_permission_id').val(permission.id);
                $('#edit_permission_name').val(permission.name);
                $('#edit_guard_name').val(permission.guard_name);
                
                // Build role checkboxes
                var permissionRoleIds = permission.roles ? permission.roles.map(r => r.id) : [];
                var checkboxesHtml = '';
                
                availableRoles.forEach(function(role) {
                    var isChecked = permissionRoleIds.includes(role.id);
                    var labelClass = role.name === 'super-admin' ? 'danger' : 
                                   (role.name === 'admin' ? 'warning' : 'info');
                    
                    checkboxesHtml += '<div class="checkbox">';
                    checkboxesHtml += '  <label>';
                    checkboxesHtml += '    <input type="checkbox" name="roles[]" value="' + role.id + '" ' + (isChecked ? 'checked' : '') + '> ';
                    checkboxesHtml += '    <span class="label label-' + labelClass + '">' + role.name + '</span>';
                    checkboxesHtml += '    <small class="text-muted"> (' + (role.permissions_count || 0) + ' permissions)</small>';
                    checkboxesHtml += '  </label>';
                    checkboxesHtml += '</div>';
                });
                
                $('#edit_permission_role_checkboxes').html(checkboxesHtml);
                
                // Show modal
                $('#editPermissionModal').modal('show');
            }
        },
        error: function(xhr) {
            alert('Error loading permission details: ' + (xhr.responseJSON?.message || 'Unknown error'));
        }
    });
}

// Handle Edit Form Submission
$('#editPermissionForm').on('submit', function(e) {
    e.preventDefault();
    
    var permissionId = $('#edit_permission_id').val();
    
    // Collect selected role IDs
    var selectedRoles = [];
    $('input[name="roles[]"]:checked').each(function() {
        selectedRoles.push($(this).val());
    });
    
    var formData = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        _method: 'PUT',
        name: $('#edit_permission_name').val(),
        guard_name: $('#edit_guard_name').val(),
        roles: selectedRoles
    };
    
    $.ajax({
        url: '/system/permissions/' + permissionId,
        type: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                $('#editPermissionModal').modal('hide');
                
                // Show success message with details
                var message = response.message;
                if (response.roles_updated) {
                    message += '\n\nRole Assignments Updated:';
                    if (response.roles_added && response.roles_added.length > 0) {
                        message += '\n✓ Added to: ' + response.roles_added.join(', ');
                    }
                    if (response.roles_removed && response.roles_removed.length > 0) {
                        message += '\n✗ Removed from: ' + response.roles_removed.join(', ');
                    }
                }
                
                alert(message);
                location.reload(); // Reload to show updated data
            }
        },
        error: function(xhr) {
            var errorMsg = 'Error updating permission';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg += ': ' + xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                errorMsg += ':\n' + Object.values(xhr.responseJSON.errors).join('\n');
            }
            alert(errorMsg);
        }
    });
});

// Delete Permission Function
function deletePermission(permissionId) {
    if (confirm('Are you sure you want to delete this permission? This action cannot be undone.')) {
        $.ajax({
            url: '/system/permissions/' + permissionId,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'DELETE'
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload(); // Reload to show updated list
                }
            },
            error: function(xhr) {
                var errorMsg = 'Error deleting permission';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg += ': ' + xhr.responseJSON.message;
                }
                alert(errorMsg);
            }
        });
    }
}

// Initialize DataTables on document ready
$(document).ready(function() {
    // Initialize permissions table with enhanced features
    $('#permissions-table').DataTable({
        "responsive": true,
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "order": [[0, "asc"]], // Order by ID column
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search permissions...",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ permissions",
            "infoEmpty": "No permissions available",
            "infoFiltered": "(filtered from _MAX_ total permissions)",
            "emptyTable": "No permissions found in the system",
            "zeroRecords": "No matching permissions found"
        },
        "dom": '<"row"<"col-sm-6"l><"col-sm-6"f>>' +
               '<"row"<"col-sm-12"tr>>' +
               '<"row"<"col-sm-5"i><"col-sm-7"p>>',
        "columnDefs": [
            {
                "targets": [0], // ID column
                "className": "text-center"
            },
            {
                "targets": [3, 4], // Created/Updated columns
                "type": "date"
            },
            {
                "targets": [5], // Actions column
                "orderable": false,
                "searchable": false,
                "className": "text-center"
            }
        ]
    });

    // Initialize Select2 on dropdowns
    $('.select2').select2({
        theme: 'bootstrap',
        width: '100%',
        placeholder: function(){
            $(this).data('placeholder');
        }
    });
});
</script>
@endsection
