@extends('layouts.app')

@section('main-content')

@include('components.page-header', [
    'title' => 'User Management',
    'subtitle' => 'Manage system users and their roles',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('admin.dashboard'), 'icon' => 'home'],
        ['label' => 'Users']
    ],
    'actions' => auth()->user()->can('create-users') ? 
        '<a href="'.route('users.create').'" class="btn btn-primary">
            <i class="fa fa-plus"></i> Add New User
        </a>' : ''
])

<div class="content-wrapper">
    <section class="content">
        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fa fa-check"></i> {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fa fa-ban"></i> {{ session('error') }}
        </div>
        @endif

        {{-- Quick Stats Cards --}}
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua" onclick="filterByStatus('all')">
                    <div class="inner">
                        <h3>{{ method_exists($users, 'total') ? $users->total() : count($users) }}</h3>
                        <p>Total Users</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-users"></i>
                    </div>
                    <a href="#" class="small-box-footer" onclick="event.preventDefault(); filterByStatus('all')">
                        View All <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green" onclick="filterByStatus('active')">
                    <div class="inner">
                        <h3>{{ $users->filter(function($u) { return $u->is_active ?? true; })->count() }}</h3>
                        <p>Active Users</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <a href="#" class="small-box-footer" onclick="event.preventDefault(); filterByStatus('active')">
                        Filter Active <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow" onclick="filterByStatus('inactive')">
                    <div class="inner">
                        <h3>{{ $users->filter(function($u) { return !($u->is_active ?? true); })->count() }}</h3>
                        <p>Inactive Users</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-ban"></i>
                    </div>
                    <a href="#" class="small-box-footer" onclick="event.preventDefault(); filterByStatus('inactive')">
                        Filter Inactive <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red" onclick="filterByRole('admin')">
                    <div class="inner">
                        <h3>{{ $users->filter(function($u) { return $u->roles->contains('name', 'admin') || $u->roles->contains('name', 'super-admin'); })->count() }}</h3>
                        <p>Admin Users</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-user-shield"></i>
                    </div>
                    <a href="#" class="small-box-footer" onclick="event.preventDefault(); filterByRole('admin')">
                        Filter Admins <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">

                {{-- Advanced Filters --}}
                <div class="box box-default collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-filter"></i> Advanced Filters</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse" id="filter-toggle-btn">
                                <i class="fa fa-plus"></i> <span class="filter-toggle-text">Expand Filters</span>
                            </button>
                        </div>
                    </div>
                    <div class="box-body filter-bar">
                        <form id="filterForm" method="GET" action="{{ route('users.index') }}">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fa fa-search"></i> Search</label>
                                        <input type="text" name="search" id="search" class="form-control" 
                                               placeholder="Name, Email, or ID" value="{{ request('search') }}">
                                        <small class="text-muted">Search by name, email, or user ID</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fa fa-user-tag"></i> Role</label>
                                        <select name="role" id="role_filter" class="form-control">
                                            <option value="">All Roles</option>
                                            @foreach($roles as $role)
                                            <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('-', ' ', $role->name)) }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fa fa-sitemap"></i> Division</label>
                                        <select name="division" id="division_filter" class="form-control">
                                            <option value="">All Divisions</option>
                                            @php
                                                $divisions = \App\Division::orderBy('name')->get();
                                            @endphp
                                            @foreach($divisions as $division)
                                            <option value="{{ $division->id }}" {{ request('division') == $division->id ? 'selected' : '' }}>
                                                {{ $division->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fa fa-toggle-on"></i> Status</label>
                                        <select name="status" id="status_filter" class="form-control">
                                            <option value="">All Statuses</option>
                                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fa fa-calendar"></i> Created From</label>
                                        <input type="date" name="created_from" class="form-control" value="{{ request('created_from') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fa fa-calendar"></i> Created To</label>
                                        <input type="date" name="created_to" class="form-control" value="{{ request('created_to') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fa fa-map-marker-alt"></i> Location</label>
                                        <select name="location" id="location_filter" class="form-control">
                                            <option value="">All Locations</option>
                                            @php
                                                $locations = \App\Location::orderBy('name')->get();
                                            @endphp
                                            @foreach($locations as $location)
                                            <option value="{{ $location->id }}" {{ request('location') == $location->id ? 'selected' : '' }}>
                                                {{ $location->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-filter"></i> Apply Filters
                                    </button>
                                    <a href="{{ route('users.index') }}" class="btn btn-default">
                                        <i class="fa fa-refresh"></i> Reset Filters
                                    </a>
                                    <button type="button" class="btn btn-success pull-right" id="export-filtered-btn">
                                        <i class="fa fa-file-excel"></i> Export Filtered Results
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Users Table --}}
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-users"></i> Users List
                            <span class="badge bg-blue" id="user-count">{{ method_exists($users, 'total') ? $users->total() : count($users) }}</span>
                        </h3>
                    </div>
                    <div class="box-body">
                        {{-- Bulk Operations Toolbar --}}
                        @can('delete-users')
                        <div class="bulk-operations-toolbar" id="bulk-toolbar">
                            <button id="bulk-delete-btn" class="btn btn-danger">
                                <i class="fa fa-trash"></i> Delete Selected (<span id="selected-count">0</span>)
                            </button>
                            <button id="bulk-deactivate-btn" class="btn btn-warning">
                                <i class="fa fa-ban"></i> Deactivate Selected
                            </button>
                            <button id="bulk-activate-btn" class="btn btn-success">
                                <i class="fa fa-check-circle"></i> Activate Selected
                            </button>
                        </div>
                        @endcan

                        <div class="table-responsive">
                            <table class="table table-enhanced table-bordered table-striped table-hover" id="users-table">
                                <thead>
                                    <tr>
                                        <th style="width:40px; text-align:center;"> 
                                            <input type="checkbox" id="select-all" title="Select all">
                                        </th>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Division</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Last Login</th>
                                        <th style="width: 180px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                    <tr>
                                        <td style="text-align:center; vertical-align: middle;">
                                            @if($user->id !== auth()->id())
                                            <input type="checkbox" class="row-check" data-id="{{ $user->id }}">
                                            @endif
                                        </td>
                                        <td><strong>#{{ $user->id }}</strong></td>
                                        <td>
                                            <strong>{{ $user->name }}</strong>
                                            @if($user->id === auth()->id())
                                            <span class="label label-info"><i class="fa fa-user"></i> You</span>
                                            @endif
                                            @if($user->employee_id)
                                            <br><small class="text-muted"><i class="fa fa-id-card"></i> {{ $user->employee_id }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <i class="fa fa-envelope text-muted"></i> {{ $user->email }}
                                            @if($user->phone)
                                            <br><small class="text-muted"><i class="fa fa-phone"></i> {{ $user->phone }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->division)
                                            <i class="fa fa-sitemap text-primary"></i> {{ $user->division->name }}
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @foreach($user->roles as $role)
                                            <span class="label label-{{ $role->name === 'super-admin' ? 'danger' : ($role->name === 'admin' ? 'warning' : ($role->name === 'management' ? 'info' : 'success')) }}">
                                                @if($role->name === 'super-admin')
                                                    <i class="fa fa-crown"></i>
                                                @elseif($role->name === 'admin')
                                                    <i class="fa fa-user-tie"></i>
                                                @elseif($role->name === 'management')
                                                    <i class="fa fa-briefcase"></i>
                                                @else
                                                    <i class="fa fa-user"></i>
                                                @endif
                                                {{ ucfirst(str_replace('-', ' ', $role->name)) }}
                                            </span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @if($user->is_active ?? true)
                                            <span class="label label-success"><i class="fa fa-check-circle"></i> Active</span>
                                            @else
                                            <span class="label label-danger"><i class="fa fa-ban"></i> Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <i class="fa fa-calendar text-muted"></i> {{ $user->created_at->format('M d, Y') }}
                                            <br><small class="text-muted">{{ $user->created_at->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            @if($user->last_login_at)
                                            <i class="fa fa-clock text-success"></i> {{ $user->last_login_at->diffForHumans() }}
                                            <br><small class="text-muted">{{ $user->last_login_at->format('M d, Y h:i A') }}</small>
                                            @else
                                            <span class="text-muted">Never</span>
                                            @endif
                                        </td>
                                        <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('users.show', $user) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="View Details">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ route('users.edit', $user) }}" 
                                                   class="btn btn-sm btn-primary"
                                                   title="Edit User">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                @if($user->id !== auth()->id())
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger" 
                                                        onclick="deleteUser({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                        title="Delete User">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                                @else
                                                <button type="button" 
                                                        class="btn btn-sm btn-secondary" 
                                                        disabled
                                                        title="Cannot delete yourself">
                                                    <i class="fa fa-ban"></i>
                                                </button>
                                                @endif
                                            </div>
                                            @if($user->id !== auth()->id())
                                            <form id="delete-user-{{ $user->id }}" method="POST" action="{{ route('users.destroy', $user) }}" style="display:none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" style="text-align: center; padding: 50px;">
                                            <div class="empty-state">
                                                <div style="font-size: 48px; color: #ddd; margin-bottom: 15px;">
                                                    <i class="fa fa-users"></i>
                                                </div>
                                                <h4 style="color: #999;">No Users Found</h4>
                                                <p style="color: #bbb;">
                                                    There are no users matching your search criteria.
                                                    @can('create-users')
                                                    <br>Try adjusting your filters or create a new user.
                                                    @endcan
                                                </p>
                                                @can('create-users')
                                                <a href="{{ route('users.create') }}" class="btn btn-primary btn-lg" style="margin-top: 15px;">
                                                    <i class="fa fa-plus"></i> Add New User
                                                </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Enhanced Pagination --}}
                        @if(method_exists($users, 'hasPages') && $users->hasPages())
                        <div class="row" style="margin-top: 20px;">
                            <div class="col-sm-5">
                                <div class="dataTables_info">
                                    <i class="fa fa-info-circle"></i> 
                                    Showing <strong>{{ $users->firstItem() }}</strong> to <strong>{{ $users->lastItem() }}</strong> of <strong>{{ $users->total() }}</strong> users
                                </div>
                            </div>
                            <div class="col-sm-7">
                                <div class="dataTables_paginate paging_simple_numbers" style="float: right;">
                                    {{ $users->links() }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Role Summary -->
                <div class="row">
                    @foreach($roles as $role)
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-{{ $role->name === 'super-admin' ? 'red' : ($role->name === 'admin' ? 'yellow' : ($role->name === 'management' ? 'blue' : 'green')) }}">
                                <i class="fa fa-{{ $role->name === 'super-admin' ? 'crown' : ($role->name === 'admin' ? 'user-tie' : ($role->name === 'management' ? 'briefcase' : 'user')) }}"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ ucfirst(str_replace('-', ' ', $role->name)) }}</span>
                                <span class="info-box-number">
                                    {{ $users->filter(function($user) use ($role) {
                                        return $user->roles->contains('name', $role->name);
                                    })->count() }} users
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</div>

@include('components.loading-overlay')

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    @if(count($users) > 0)
    // Initialize DataTable with Export Buttons
    var table = $('#users-table').DataTable({
        responsive: true,
        dom: 'l<"clear">Bfrtip',
        pageLength: 25,
        lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'All']],
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fa fa-file-excel-o"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: 'Users List - {{ date("Y-m-d") }}',
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] }
            },
            {
                extend: 'csv',
                text: '<i class="fa fa-file-text-o"></i> CSV',
                className: 'btn btn-info btn-sm',
                title: 'Users List - {{ date("Y-m-d") }}',
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] }
            },
            {
                extend: 'pdf',
                text: '<i class="fa fa-file-pdf-o"></i> PDF',
                className: 'btn btn-danger btn-sm',
                title: 'Users List',
                orientation: 'landscape',
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] }
            },
            {
                extend: 'copy',
                text: '<i class="fa fa-copy"></i> Copy',
                className: 'btn btn-default btn-sm',
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] }
            }
        ],
        language: {
            lengthMenu: "Show _MENU_ users per page",
            info: "Showing _START_ to _END_ of _TOTAL_ users",
            infoEmpty: "No users found",
            infoFiltered: "(filtered from _MAX_ total users)",
            search: "Search:",
            paginate: {
                first: '<i class="fa fa-angle-double-left"></i>',
                previous: '<i class="fa fa-angle-left"></i>',
                next: '<i class="fa fa-angle-right"></i>',
                last: '<i class="fa fa-angle-double-right"></i>'
            }
        },
        order: [[1, 'desc']], // Sort by ID descending
        columnDefs: [
            { orderable: false, targets: [0, 9] } // Disable sorting on checkbox and actions
        ]
    });

    // Clickable Stat Cards - Filter by Status
    window.filterByStatus = function(status) {
        if (status === 'all') {
            table.search('').draw();
        } else if (status === 'active') {
            table.search('Active').draw();
        } else if (status === 'inactive') {
            table.search('Inactive').draw();
        }
    };

    // Clickable Stat Cards - Filter by Role
    window.filterByRole = function(role) {
        if (role === 'admin') {
            table.search('admin').draw();
        }
    };

    // Export Filtered Results
    $('#export-filtered-btn').on('click', function() {
        table.button('.buttons-excel').trigger();
    });

    // Filter Toggle Animation
    $('#filter-toggle-btn').on('click', function() {
        var $icon = $(this).find('i');
        var $text = $(this).find('.filter-toggle-text');
        
        if ($icon.hasClass('fa-plus')) {
            $icon.removeClass('fa-plus').addClass('fa-minus');
            $text.text('Collapse Filters');
        } else {
            $icon.removeClass('fa-minus').addClass('fa-plus');
            $text.text('Expand Filters');
        }
    });

    // Delete confirmation
    window.deleteUser = function(id, name) {
        if(confirm('Are you sure you want to delete user "' + name + '"? This action cannot be undone.')) {
            showLoading('Deleting user...');
            document.getElementById('delete-user-' + id).submit();
        }
    };
    
    // Loading state for action buttons
    $('.table-actions a').on('click', function() {
        var action = $(this).attr('title') || 'Processing';
        showLoading(action + '...');
    });

    // Select all / row selection handling
    $('#select-all').on('change', function() {
        var checked = $(this).is(':checked');
        $('.row-check').prop('checked', checked).trigger('change');
    });

    $('.row-check').on('change', function() {
        var checkedCount = $('.row-check:checked').length;
        var anyChecked = checkedCount > 0;
        
        // Update selected count
        $('#selected-count').text(checkedCount);
        
        // Show/hide bulk toolbar
        if (anyChecked) {
            $('#bulk-toolbar').addClass('active').slideDown();
        } else {
            $('#bulk-toolbar').removeClass('active').slideUp();
        }
        
        // Update select-all checkbox state
        var totalCheckboxes = $('.row-check').length;
        $('#select-all').prop('checked', checkedCount === totalCheckboxes);
        $('#select-all').prop('indeterminate', checkedCount > 0 && checkedCount < totalCheckboxes);
    });

    // Bulk delete handler
    $('#bulk-delete-btn').on('click', function(e) {
        e.preventDefault();
        var ids = $('.row-check:checked').map(function() { return $(this).data('id'); }).get();
        if (!ids.length) return;

        if (!confirm('Are you sure you want to delete ' + ids.length + ' selected user(s)? This action cannot be undone.')) return;

        showLoading('Deleting selected users...');

        var token = '{{ csrf_token() }}';

        fetch('{{ route("users.bulk-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ ids: ids })
        }).then(function(resp) {
            if (resp.ok) return resp.json();
            throw new Error('Failed to delete');
        }).then(function(json) {
            hideLoading();
            if (json.success) {
                // Remove deleted rows from table
                ids.forEach(function(id) {
                    var row = table.row($('.row-check[data-id="' + id + '"]').closest('tr'));
                    row.remove();
                });
                table.draw();
                
                // Reset checkboxes
                $('#select-all').prop('checked', false);
                $('#bulk-toolbar').removeClass('active').slideUp();
                
                // Show success message
                alert('Successfully deleted ' + ids.length + ' user(s)');
                
                // Reload page to update counts
                location.reload();
            } else {
                alert(json.message || 'Failed to delete users');
            }
        }).catch(function(err) {
            hideLoading();
            console.error(err);
            alert('An error occurred while deleting users');
        });
    });

    // Bulk activate handler
    $('#bulk-activate-btn').on('click', function(e) {
        e.preventDefault();
        var ids = $('.row-check:checked').map(function() { return $(this).data('id'); }).get();
        if (!ids.length) return;

        if (!confirm('Are you sure you want to activate ' + ids.length + ' selected user(s)?')) return;

        showLoading('Activating selected users...');
        
        // Implementation would require a bulk-activate route
        alert('Bulk activate functionality requires backend implementation');
        hideLoading();
    });

    // Bulk deactivate handler
    $('#bulk-deactivate-btn').on('click', function(e) {
        e.preventDefault();
        var ids = $('.row-check:checked').map(function() { return $(this).data('id'); }).get();
        if (!ids.length) return;

        if (!confirm('Are you sure you want to deactivate ' + ids.length + ' selected user(s)?')) return;

        showLoading('Deactivating selected users...');
        
        // Implementation would require a bulk-deactivate route
        alert('Bulk deactivate functionality requires backend implementation');
        hideLoading();
    });
    @endif

    // Auto-dismiss alerts after 5 seconds
    $('.alert').delay(5000).slideUp(300);
});
</script>
@endpush
