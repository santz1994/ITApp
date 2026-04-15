@extends('layouts.app')

@section('main-content')
<div class="container-fluid">
    <!-- Page Title -->
    <div class="row">
        <div class="col-md-12">
            <h1 class="page-header">
                <i class="fa fa-bars"></i> Menu Management
                <small>Manage application menus and permissions</small>
            </h1>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{ $totalMenus }}</h3>
                    <p>Total Menus</p>
                </div>
                <div class="icon">
                    <i class="fa fa-bars"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ $activeMenus }}</h3>
                    <p>Active Menus</p>
                </div>
                <div class="icon">
                    <i class="fa fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ $totalMenus - $activeMenus }}</h3>
                    <p>Inactive Menus</p>
                </div>
                <div class="icon">
                    <i class="fa fa-times-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{ \App\Role::count() }}</h3>
                    <p>Total Roles</p>
                </div>
                <div class="icon">
                    <i class="fa fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <!-- Main Menu Table -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-list"></i> Menu List
                    </h3>
                    <div class="box-tools">
                        <div class="btn-group">
                            <a href="{{ route('admin.menus.create') }}" class="btn btn-success">
                                <i class="fa fa-plus-circle"></i> Add New Menu
                            </a>
                            <button type="button" class="btn btn-info" id="btn-expand-all">
                                <i class="fa fa-expand"></i> Expand All
                            </button>
                            <button type="button" class="btn btn-default" id="btn-collapse-all">
                                <i class="fa fa-compress"></i> Collapse All
                            </button>
                            <button type="button" class="btn btn-warning" id="btn-clear-cache">
                                <i class="fa fa-refresh"></i> Clear Cache
                            </button>
                        </div>
                    </div>
                </div>
                <div class="box-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="fa fa-check-circle"></i> <strong>Success!</strong> {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="fa fa-exclamation-triangle"></i> <strong>Error!</strong> {{ session('error') }}
                        </div>
                    @endif

                    <!-- Filter & Search -->
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                <input type="text" class="form-control" id="search-menu" placeholder="Search menu by label, route, or URL...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="filter-status">
                                <option value="">All Status</option>
                                <option value="active">Active Only</option>
                                <option value="inactive">Inactive Only</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="filter-level">
                                <option value="">All Levels</option>
                                <option value="0">Top Level Only</option>
                                <option value="1">With Children</option>
                            </select>
                        </div>
                    </div>

                    <!-- Menu Hierarchy Tree -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="menus-table">
                            <thead class="bg-light-blue">
                                <tr>
                                    <th width="5%" class="text-center">ID</th>
                                    <th width="30%">Menu Label</th>
                                    <th width="20%">Route / URL</th>
                                    <th width="10%" class="text-center">Icon</th>
                                    <th width="8%" class="text-center">Order</th>
                                    <th width="10%" class="text-center">Status</th>
                                    <th width="17%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($menus as $menu)
                                    @include('admin.menus.partials.menu-row', ['menu' => $menu, 'level' => 0])
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center" style="padding: 40px;">
                                            <i class="fa fa-info-circle fa-3x text-muted"></i>
                                            <h4 class="text-muted">No menus found</h4>
                                            <p class="text-muted">Get started by creating your first menu</p>
                                            <a href="{{ route('admin.menus.create') }}" class="btn btn-primary">
                                                <i class="fa fa-plus"></i> Create First Menu
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="box-footer">
                    <div class="row">
                        <div class="col-sm-6">
                            <p class="text-muted" style="margin: 8px 0;">
                                <i class="fa fa-info-circle"></i> 
                                Showing <strong>{{ $menus->count() }}</strong> top-level menus
                            </p>
                        </div>
                        <div class="col-sm-6 text-right">
                            <a href="{{ route('home') }}" class="btn btn-default">
                                <i class="fa fa-dashboard"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Guide -->
            <div class="box box-info collapsed-box">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-question-circle"></i> Quick Guide & Tips
                    </h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4><i class="fa fa-lightbulb-o text-yellow"></i> Features</h4>
                            <ul class="list-unstyled">
                                <li><i class="fa fa-check text-success"></i> <strong>Hierarchical Structure:</strong> Unlimited nested levels</li>
                                <li><i class="fa fa-check text-success"></i> <strong>Role-Based Access:</strong> Control visibility per role</li>
                                <li><i class="fa fa-check text-success"></i> <strong>FontAwesome Icons:</strong> 600+ icons available</li>
                                <li><i class="fa fa-check text-success"></i> <strong>Performance Cached:</strong> 1-hour cache for speed</li>
                                <li><i class="fa fa-check text-success"></i> <strong>External Links:</strong> Support for external URLs</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h4><i class="fa fa-keyboard-o text-blue"></i> Quick Actions</h4>
                            <table class="table table-condensed">
                                <tr>
                                    <td><span class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></span></td>
                                    <td><strong>Edit:</strong> Modify menu details and settings</td>
                                </tr>
                                <tr>
                                    <td><span class="btn btn-xs btn-info"><i class="fa fa-shield"></i></span></td>
                                    <td><strong>Permissions:</strong> Manage role-based access</td>
                                </tr>
                                <tr>
                                    <td><span class="btn btn-xs btn-success"><i class="fa fa-toggle-on"></i></span></td>
                                    <td><strong>Toggle:</strong> Enable/disable without deleting</td>
                                </tr>
                                <tr>
                                    <td><span class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></span></td>
                                    <td><strong>Delete:</strong> Remove menu and all children</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="alert alert-warning" style="margin-top: 15px; margin-bottom: 0;">
                        <i class="fa fa-exclamation-triangle"></i> <strong>Note:</strong> 
                        Changes to menu structure require cache clearing to take effect. 
                        Use the "Clear Cache" button above or logout/login to see changes.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-warning"></i> Confirm Deletion
                </h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i>
                    <strong>Warning!</strong> This action cannot be undone.
                </div>
                <p>Are you sure you want to delete this menu?</p>
                <p class="text-danger">
                    <i class="fa fa-info-circle"></i>
                    <strong>Note:</strong> All child menus will also be deleted permanently.
                </p>
                <div class="well well-sm">
                    <strong>Menu:</strong> <span id="delete-menu-name"></span>
                </div>
            </div>
            <div class="modal-footer">
                <form id="delete-form" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-trash"></i> Yes, Delete Permanently
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Page Header */
    .page-header {
        border-bottom: 2px solid #3c8dbc;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
    
    /* Menu Row Levels */
    .menu-row-level-1 { 
        padding-left: 40px !important;
        background-color: #f9f9f9;
    }
    .menu-row-level-2 { 
        padding-left: 80px !important;
        background-color: #f5f5f5;
    }
    .menu-row-level-3 { 
        padding-left: 120px !important;
        background-color: #f0f0f0;
    }
    .menu-row-level-4 { 
        padding-left: 160px !important;
        background-color: #ebebeb;
    }
    
    /* Menu Icon Preview */
    .menu-icon-preview {
        display: inline-block;
        width: 25px;
        height: 25px;
        text-align: center;
        line-height: 25px;
        font-size: 16px;
        border-radius: 3px;
        background: #f4f4f4;
        margin-right: 5px;
    }
    
    /* Status Badges */
    .badge-status {
        font-size: 11px;
        padding: 5px 10px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    /* Action Buttons */
    .btn-group-actions {
        display: flex;
        gap: 3px;
        justify-content: center;
    }
    
    .btn-group-actions .btn {
        padding: 5px 10px;
        font-size: 12px;
        border-radius: 3px;
    }
    
    /* Table Enhancements */
    #menus-table thead {
        background: linear-gradient(to bottom, #3c8dbc 0%, #367fa9 100%);
        color: white;
    }
    
    #menus-table tbody tr {
        transition: all 0.2s ease;
    }
    
    #menus-table tbody tr:hover {
        background-color: #f0f8ff !important;
        transform: scale(1.01);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    #menus-table tbody tr.collapsed-row {
        display: none;
    }
    
    /* Small Boxes */
    .small-box {
        border-radius: 5px;
        position: relative;
        display: block;
        margin-bottom: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .small-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .small-box > .inner {
        padding: 15px;
    }
    
    .small-box h3 {
        font-size: 38px;
        font-weight: bold;
        margin: 0 0 10px 0;
        white-space: nowrap;
        padding: 0;
    }
    
    .small-box p {
        font-size: 14px;
    }
    
    .small-box .icon {
        position: absolute;
        top: 10px;
        right: 15px;
        z-index: 0;
        font-size: 90px;
        color: rgba(0,0,0,0.1);
    }
    
    /* Search & Filter */
    #search-menu {
        border-radius: 4px;
        height: 38px;
    }
    
    .input-group-addon {
        background-color: #3c8dbc;
        color: white;
        border-color: #3c8dbc;
    }
    
    /* Modal Enhancements */
    .modal-header.bg-danger {
        background-color: #dd4b39;
        color: white;
        border-radius: 5px 5px 0 0;
    }
    
    /* Toggle Children Button */
    .btn-toggle-children {
        background: transparent;
        border: none;
        color: #3c8dbc;
        cursor: pointer;
        padding: 0;
        margin-right: 5px;
    }
    
    .btn-toggle-children:hover {
        color: #23527c;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .btn-group-actions {
            flex-wrap: wrap;
        }
        
        .small-box .icon {
            font-size: 60px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Search Functionality
    $('#search-menu').on('keyup', function() {
        var searchText = $(this).val().toLowerCase();
        $('#menus-table tbody tr').each(function() {
            var rowText = $(this).text().toLowerCase();
            if (rowText.indexOf(searchText) === -1) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
    });
    
    // Filter by Status
    $('#filter-status').on('change', function() {
        var status = $(this).val();
        $('#menus-table tbody tr').show();
        
        if (status === 'active') {
            $('#menus-table tbody tr:contains("Inactive")').hide();
        } else if (status === 'inactive') {
            $('#menus-table tbody tr:contains("Active")').not(':contains("Inactive")').hide();
        }
    });
    
    // Expand All
    $('#btn-expand-all').on('click', function() {
        $('#menus-table tbody tr').removeClass('collapsed-row').show();
        $('.btn-toggle-children i').removeClass('fa-plus-square').addClass('fa-minus-square');
    });
    
    // Collapse All
    $('#btn-collapse-all').on('click', function() {
        $('#menus-table tbody tr[class*="menu-row-level-"]').addClass('collapsed-row').hide();
        $('.btn-toggle-children i').removeClass('fa-minus-square').addClass('fa-plus-square');
    });
    
    // Toggle Children Rows
    $(document).on('click', '.btn-toggle-children', function(e) {
        e.preventDefault();
        var menuId = $(this).data('menu-id');
        var $icon = $(this).find('i');
        var $row = $(this).closest('tr');
        var level = parseInt($row.attr('data-level'));
        
        // Find all direct child rows
        var $nextRow = $row.next('tr');
        var childRows = [];
        
        while ($nextRow.length > 0) {
            var nextLevel = parseInt($nextRow.attr('data-level'));
            var nextParentId = $nextRow.attr('data-parent-id');
            
            // Break if we've reached a sibling or parent
            if (nextLevel <= level) {
                break;
            }
            
            // Add direct children
            if (nextLevel === level + 1 && nextParentId == menuId) {
                childRows.push($nextRow);
            }
            
            // Add all descendants
            if (nextLevel > level) {
                childRows.push($nextRow);
            }
            
            $nextRow = $nextRow.next('tr');
        }
        
        if ($icon.hasClass('fa-plus-square')) {
            // Expand: show direct children only
            $icon.removeClass('fa-plus-square').addClass('fa-minus-square');
            $.each(childRows, function(i, $row) {
                var rowLevel = parseInt($row.attr('data-level'));
                if (rowLevel === level + 1) {
                    $row.show().removeClass('collapsed-row');
                }
            });
        } else {
            // Collapse: hide all children and descendants
            $icon.removeClass('fa-minus-square').addClass('fa-plus-square');
            $.each(childRows, function(i, $row) {
                $row.hide().addClass('collapsed-row');
                // Also collapse their toggle buttons
                $row.find('.btn-toggle-children i')
                    .removeClass('fa-minus-square')
                    .addClass('fa-plus-square');
            });
        }
    });
    
    // Toggle Active/Inactive Status
    $('.btn-toggle-status').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var menuId = $btn.data('id');
        var currentStatus = $btn.data('status');
        
        // Show loading
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: '/admin/menus/' + menuId + '/toggle-active',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Menu status updated successfully!');
                    } else {
                        alert('Menu status updated successfully!');
                    }
                    // Reload after short delay
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Error: ' + response.message);
                    } else {
                        alert('Error: ' + response.message);
                    }
                    $btn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Error toggling menu status');
                } else {
                    alert('Error toggling menu status');
                }
                $btn.prop('disabled', false);
            }
        });
    });
    
    // Delete Menu
    $('.btn-delete-menu').on('click', function(e) {
        e.preventDefault();
        var menuId = $(this).data('id');
        var menuName = $(this).data('name');
        
        $('#delete-menu-name').text(menuName);
        $('#delete-form').attr('action', '/admin/menus/' + menuId);
        $('#deleteModal').modal('show');
    });
    
    // Clear Cache
    $('#btn-clear-cache').on('click', function() {
        var $btn = $(this);
        
        if (confirm('Clear all menu cache? This will refresh menu display for all users.')) {
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Clearing...');
            
            $.ajax({
                url: '{{ route("admin.menus.clear-cache") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Cache cleared successfully!');
                    } else {
                        alert('Cache cleared successfully!');
                    }
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                },
                error: function() {
                    if (typeof toastr !== 'undefined') {
                        toastr.warning('Cache may need manual clearing. Run: php artisan cache:clear');
                    } else {
                        alert('Cache may need manual clearing. Run: php artisan cache:clear');
                    }
                    $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Clear Cache');
                }
            });
        }
    });
    
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endpush
