@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard-widgets.css') }}">
@endpush

@section('main-content')
    @include('components.page-header', [
        'title' => 'Admin Dashboard',
        'subtitle' => 'System overview and quick actions',
        'icon' => 'fa-tachometer',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => url('/home'), 'icon' => 'fa-dashboard'],
            ['label' => 'Admin Dashboard', 'active' => true]
        ]
    ])

    @include('components.loading-overlay')

    <section class="content">
        <!-- Modern KPI Cards -->
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="{{ route('users.index') }}" style="text-decoration: none; color: inherit;">
                    <div class="kpi-card">
                        <div class="kpi-icon bg-primary">
                            <i class="fa fa-users"></i>
                        </div>
                        <div class="kpi-content">
                            <h3 class="kpi-value">{{ $stats['total_users'] ?? 0 }}</h3>
                            <p class="kpi-label">Total Users</p>
                            @if(isset($stats['users_growth']) && $stats['users_growth'] > 0)
                            <span class="kpi-trend positive">
                                <i class="fa fa-arrow-up"></i> {{ $stats['users_growth'] }}% this month
                            </span>
                            @endif
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="{{ route('vehicles.index') }}" style="text-decoration: none; color: inherit;">
                    <div class="kpi-card">
                        <div class="kpi-icon bg-danger">
                            <i class="fa fa-car"></i>
                        </div>
                        <div class="kpi-content">
                            <h3 class="kpi-value">{{ $stats['total_vehicles'] ?? 0 }}</h3>
                            <p class="kpi-label">Total Vehicles</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="{{ route('inventory.index') }}" style="text-decoration: none; color: inherit;">
                    <div class="kpi-card">
                        <div class="kpi-icon bg-success">
                            <i class="fa fa-cubes"></i>
                        </div>
                        <div class="kpi-content">
                            <h3 class="kpi-value">{{ $stats['total_inventory_items'] ?? 0 }}</h3>
                            <p class="kpi-label">Inventory Items</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="{{ route('approvals.pending') }}" style="text-decoration: none; color: inherit;">
                    <div class="kpi-card">
                        <div class="kpi-icon bg-warning">
                            <i class="fa fa-check-circle"></i>
                        </div>
                        <div class="kpi-content">
                            <h3 class="kpi-value">{{ $stats['pending_approvals'] ?? 0 }}</h3>
                            <p class="kpi-label">Pending Approvals</p>
                            <span class="kpi-trend neutral">
                                <i class="fa fa-clock-o"></i> Requires attention
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Security Status Alert -->
        <div class="row">
            <div class="col-md-12">
                @if(auth()->user()->email === 'daniel@quty.co.id')
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-shield"></i> Full Administrative Access</h4>
                    You have unrestricted access to all administrative functions including database modifications.
                    @if(session('admin_password_confirmed') && session('admin_password_confirmed') > now()->subMinutes(30))
                        <br><small><i class="fa fa-check-circle"></i> Password authentication valid until {{ session('admin_password_confirmed')->addMinutes(30)->format('H:i:s') }}</small>
                        <a href="{{ route('admin.clear-auth') }}" class="btn btn-sm btn-default pull-right" onclick="return confirm('Clear authentication session?')">
                            <i class="fa fa-times"></i> Clear Auth
                        </a>
                    @endif
                </div>
                @else
                <div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-warning"></i> Limited Administrative Access</h4>
                    Your account ({{ auth()->user()->email }}) has read-only admin access. 
                    Database modifications are restricted to authorized personnel (daniel@quty.co.id).
                </div>
                @endif
            </div>
        </div>

        <div class="row">
            <!-- System Status -->
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-cog"></i> System Status
                        </h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-condensed">
                            <tr>
                                <td><strong>Database:</strong></td>
                                <td>
                                    <span class="label label-success">
                                        <i class="fa fa-check"></i> Connected
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Cache:</strong></td>
                                <td>
                                    <span class="label label-{{ $system_status['cache'] ? 'success' : 'warning' }}">
                                        <i class="fa fa-{{ $system_status['cache'] ? 'check' : 'exclamation-triangle' }}"></i> 
                                        {{ $system_status['cache'] ? 'Working' : 'Issues' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Storage:</strong></td>
                                <td>
                                    <span class="label label-{{ $system_status['storage'] ? 'success' : 'danger' }}">
                                        <i class="fa fa-{{ $system_status['storage'] ? 'check' : 'times' }}"></i> 
                                        {{ $system_status['storage'] ? 'Writable' : 'Read-only' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>PHP Version:</strong></td>
                                <td><span class="label label-info">{{ PHP_VERSION }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Laravel Version:</strong></td>
                                <td><span class="label label-info">{{ app()->version() }}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-md-6">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-flash"></i> Quick Actions
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-xs-6">
                                <a href="{{ route('users.index') }}" class="btn btn-app">
                                    <i class="fa fa-users"></i> Manage Users
                                </a>
                            </div>
                            <div class="col-xs-6">
                                <a href="{{ route('system.settings') }}" class="btn btn-app">
                                    <i class="fa fa-cogs"></i> System Settings
                                </a>
                            </div>
                            <div class="col-xs-6">
                                <a href="{{ route('admin.database.index') }}" class="btn btn-app">
                                    <i class="fa fa-database"></i> Database
                                    @if(auth()->user()->email !== 'daniel@quty.co.id')
                                        <span class="badge bg-orange"><i class="fa fa-eye"></i></span>
                                    @else
                                        <span class="badge bg-green"><i class="fa fa-edit"></i></span>
                                    @endif
                                </a>
                            </div>
                            <div class="col-xs-6">
                                @if(auth()->user()->email === 'daniel@quty.co.id')
                                <a href="{{ route('admin.cache') }}" class="btn btn-app">
                                    <i class="fa fa-refresh"></i> Clear Cache
                                    <span class="badge bg-green"><i class="fa fa-unlock"></i></span>
                                </a>
                                @else
                                <span class="btn btn-app disabled" title="Restricted to daniel@quty.co.id">
                                    <i class="fa fa-refresh"></i> Clear Cache
                                    <span class="badge bg-red"><i class="fa fa-lock"></i></span>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Permissions & Roles Management -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-key"></i> Permissions & Roles Management
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <!-- Permissions Card -->
                            <div class="col-md-4">
                                <div class="info-box bg-aqua">
                                    <span class="info-box-icon"><i class="fa fa-shield"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">System Permissions</span>
                                        <span class="info-box-number">{{ $stats['total_permissions'] ?? 97 }}</span>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: 100%"></div>
                                        </div>
                                        <span class="progress-description">
                                            All available permissions
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ route('system.permissions') }}" class="btn btn-block btn-primary">
                                    <i class="fa fa-shield"></i> Manage Permissions
                                </a>
                            </div>

                            <!-- Roles Card -->
                            <div class="col-md-4">
                                <div class="info-box bg-green">
                                    <span class="info-box-icon"><i class="fa fa-users"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">User Roles</span>
                                        <span class="info-box-number">{{ $stats['total_roles'] ?? 6 }}</span>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: 100%"></div>
                                        </div>
                                        <span class="progress-description">
                                            Active role definitions
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ route('system.roles') }}" class="btn btn-block btn-success">
                                    <i class="fa fa-users"></i> Manage Roles
                                </a>
                            </div>

                            <!-- Role Assignments Card -->
                            <div class="col-md-4">
                                <div class="info-box bg-yellow">
                                    <span class="info-box-icon"><i class="fa fa-link"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Role Assignments</span>
                                        <span class="info-box-number">{{ $stats['users_with_roles'] ?? $stats['total_users'] }}</span>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: {{ isset($stats['total_users']) && $stats['total_users'] > 0 ? round(($stats['users_with_roles'] ?? $stats['total_users']) / $stats['total_users'] * 100) : 100 }}%"></div>
                                        </div>
                                        <span class="progress-description">
                                            Users with assigned roles
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ route('users.index') }}" class="btn btn-block btn-warning">
                                    <i class="fa fa-user-plus"></i> Assign User Roles
                                </a>
                            </div>
                        </div>

                        <!-- Permission Matrix Quick View -->
                        <div class="row" style="margin-top: 20px;">
                            <div class="col-md-12">
                                <h4><i class="fa fa-table"></i> Role Permissions Overview</h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-condensed table-hover">
                                        <thead>
                                            <tr class="bg-gray">
                                                <th>Role</th>
                                                <th class="text-center">Permissions Count</th>
                                                <th class="text-center">Users</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $roles = \Spatie\Permission\Models\Role::withCount(['permissions', 'users'])->get();
                                            @endphp
                                            @foreach($roles as $role)
                                            <tr>
                                                <td>
                                                    <strong>
                                                        <span class="label label-{{ $role->name === 'super-admin' ? 'danger' : ($role->name === 'admin' ? 'warning' : 'info') }}">
                                                            {{ ucfirst($role->name) }}
                                                        </span>
                                                    </strong>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-blue">{{ $role->permissions_count }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-green">{{ $role->users_count }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="label label-success">
                                                        <i class="fa fa-check-circle"></i> Active
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-xs">
                                                        <a href="{{ route('system.roles') }}" class="btn btn-default" title="View Details">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('system.permissions') }}" class="btn btn-primary" title="Edit Permissions">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Access Links -->
                        <div class="row" style="margin-top: 15px;">
                            <div class="col-md-12">
                                <div class="callout callout-info">
                                    <h4><i class="fa fa-info-circle"></i> Quick Access</h4>
                                    <p>
                                        <a href="{{ route('system.permissions') }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-shield"></i> View All Permissions
                                        </a>
                                        <a href="{{ route('system.roles') }}" class="btn btn-sm btn-success">
                                            <i class="fa fa-users"></i> View All Roles
                                        </a>
                                        <a href="{{ route('users.index') }}" class="btn btn-sm btn-warning">
                                            <i class="fa fa-user"></i> Manage User Access
                                        </a>
                                        @if(file_exists(resource_path('views/docs')))
                                        <a href="#" class="btn btn-sm btn-info" onclick="alert('UAC Documentation: See docs/UAC_MATRIX_COMPLETE.md and docs/UAC_QUICK_REFERENCE.md')">
                                            <i class="fa fa-book"></i> UAC Documentation
                                        </a>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-history"></i> Recent System Activity
                        </h3>
                    </div>
                    <div class="box-body">
                        @if(isset($recent_activities) && count($recent_activities) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent_activities as $activity)
                                    <tr>
                                        <td>{{ $activity['time'] }}</td>
                                        <td>{{ $activity['user'] }}</td>
                                        <td>
                                            <span class="label label-{{ $activity['type'] }}">
                                                {{ $activity['action'] }}
                                            </span>
                                        </td>
                                        <td>{{ $activity['details'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p class="text-muted">No recent activity to display.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Hide loading overlay when page is fully loaded
    window.addEventListener('load', function() {
        setTimeout(function() {
            if (typeof hideLoadingOverlay === 'function') {
                hideLoadingOverlay();
            }
        }, 300);
    });
    
    // Add click loading to quick action buttons
    $('.btn-app').on('click', function() {
        if (!$(this).hasClass('disabled')) {
            showLoadingOverlay('Loading...');
        }
    });
    
    // Tooltip initialization
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@endpush
