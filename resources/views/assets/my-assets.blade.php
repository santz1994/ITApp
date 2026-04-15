@extends('layouts.app')

@section('main-content')

{{-- Page Header Component --}}
@component('components.page-header')
    @slot('icon') fa-briefcase @endslot
    @slot('title') My Assets @endslot
    @slot('subtitle') View and manage assets assigned to you @endslot
@endcomponent

{{-- Flash Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <i class="fa fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
    </div>
@endif

<div class="row">
    {{-- Main Content (9 columns) --}}
    <div class="col-md-9">
        
        {{-- My Assets Table Box --}}
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-list"></i> Your Assigned Assets
                    <span class="badge count-badge bg-aqua">{{ $assets->count() }}</span>
                </h3>
                <div class="box-tools pull-right">
                    @if($assets->count() > 0)
                    <button type="button" class="btn btn-success btn-sm" onclick="exportMyAssets()">
                        <i class="fa fa-file-excel-o"></i> Export to Excel
                    </button>
                    @endif
                </div>
            </div>
            <div class="box-body">
                @if($assets->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-enhanced" id="myAssetsTable">
                        <thead>
                            <tr>
                                <th>Asset Tag</th>
                                <th>Model</th>
                                <th>Serial Number</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Since</th>
                                <th>Condition</th>
                                <th>Quick Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assets as $asset)
                            <tr>
                                <td>
                                    <strong class="text-primary">{{ $asset->asset_tag }}</strong>
                                    @if($asset->qr_code)
                                    <br><small class="text-muted"><i class="fa fa-qrcode"></i> {{ $asset->qr_code }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($asset->model)
                                    <strong>{{ $asset->model->name }}</strong>
                                    @if($asset->model->manufacturer)
                                    <br><small class="text-muted"><i class="fa fa-industry"></i> {{ $asset->model->manufacturer->name }}</small>
                                    @endif
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($asset->serial)
                                    <code>{{ $asset->serial }}</code>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($asset->location)
                                    <i class="fa fa-map-marker text-primary"></i> {{ $asset->location->name }}
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($asset->status)
                                    <span class="label" style="background-color: {{ $asset->status->color ?? '#999' }}">
                                        {{ $asset->status->name }}
                                    </span>
                                    @else
                                    <span class="label label-default">Unknown</span>
                                    @endif
                                </td>
                                <td>
                                    @if($asset->assigned_at)
                                    {{ $asset->assigned_at->format('d M Y') }}
                                    <br><small class="text-muted">{{ $asset->assigned_at->diffForHumans() }}</small>
                                    @else
                                    <small class="text-muted">N/A</small>
                                    @endif
                                </td>
                                <td>
                                    <select class="form-control input-sm condition-select" data-asset-id="{{ $asset->id }}" style="width: 100%;">
                                        <option value="Good" {{ (optional($asset->condition)->condition ?? 'Good') == 'Good' ? 'selected' : '' }}>✓ Good</option>
                                        <option value="Fair" {{ (optional($asset->condition)->condition ?? '') == 'Fair' ? 'selected' : '' }}>⚠ Fair</option>
                                        <option value="Poor" {{ (optional($asset->condition)->condition ?? '') == 'Poor' ? 'selected' : '' }}>✗ Poor</option>
                                        <option value="Needs Attention" {{ (optional($asset->condition)->condition ?? '') == 'Needs Attention' ? 'selected' : '' }}>🔧 Needs Attention</option>
                                    </select>
                                </td>
                                <td style="white-space: nowrap;">
                                    <div class="btn-group btn-group-xs">
                                        <a href="{{ url('/assets/' . $asset->id) }}" class="btn btn-info" title="View Details">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ url('/tickets/create') }}?asset_id={{ $asset->id }}" class="btn btn-warning" title="Create Ticket">
                                            <i class="fa fa-ticket"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger" onclick="reportIssue({{ $asset->id }}, '{{ addslashes($asset->asset_tag) }}')" title="Report Issue">
                                            <i class="fa fa-exclamation-triangle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="empty-state">
                    <i class="fa fa-briefcase fa-3x text-muted"></i>
                    <p class="lead">No Assets Assigned</p>
                    <p class="text-muted">You don't have any assets assigned to you at the moment.</p>
                    <p class="text-muted">Contact your administrator if you need equipment.</p>
                </div>
                @endif
            </div>
        </div>
        
    </div>
    
    {{-- Sidebar (3 columns) --}}
    <div class="col-md-3">
        
        {{-- Asset Statistics --}}
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bar-chart"></i> My Asset Statistics</h3>
            </div>
            <div class="box-body">
                <div class="info-box bg-aqua">
                    <span class="info-box-icon"><i class="fa fa-briefcase"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Assets</span>
                        <span class="info-box-number">{{ $assets->count() }}</span>
                    </div>
                </div>
                
                @php
                    $conditions = $assets->pluck('condition')->countBy();
                    $goodCount = $conditions->get('Good', 0);
                    $fairCount = $conditions->get('Fair', 0);
                    $poorCount = $conditions->get('Poor', 0);
                    $needsAttentionCount = $conditions->get('Needs Attention', 0);
                @endphp
                
                @if($assets->count() > 0)
                <div class="info-box bg-green">
                    <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Good Condition</span>
                        <span class="info-box-number">{{ $goodCount }}</span>
                    </div>
                </div>
                
                @if($poorCount > 0 || $needsAttentionCount > 0)
                <div class="info-box bg-red">
                    <span class="info-box-icon"><i class="fa fa-exclamation-triangle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Needs Attention</span>
                        <span class="info-box-number">{{ $poorCount + $needsAttentionCount }}</span>
                    </div>
                </div>
                @endif
                @endif
            </div>
        </div>
        
        {{-- Asset Responsibility --}}
        <div class="box box-solid box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-shield"></i> Your Responsibility</h3>
            </div>
            <div class="box-body">
                <p><strong>As an asset holder, you are responsible for:</strong></p>
                <ul class="list-unstyled">
                    <li><i class="fa fa-check text-success"></i> Proper care and maintenance</li>
                    <li><i class="fa fa-check text-success"></i> Reporting damage immediately</li>
                    <li><i class="fa fa-check text-success"></i> Using equipment appropriately</li>
                    <li><i class="fa fa-check text-success"></i> Returning when leaving</li>
                </ul>
                
                @if($assets->count() > 0)
                <hr>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="acknowledgeResponsibility">
                        I acknowledge my responsibility for these assets
                    </label>
                </div>
                @endif
            </div>
        </div>
        
        {{-- Asset Care Guidelines --}}
        <div class="box box-solid box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-lightbulb-o"></i> Care Guidelines</h3>
            </div>
            <div class="box-body">
                <p><strong>Best Practices:</strong></p>
                <ul style="font-size: 13px; line-height: 1.8;">
                    <li><strong>Keep it clean</strong> - Wipe down regularly</li>
                    <li><strong>Avoid liquids</strong> - Keep drinks away</li>
                    <li><strong>Proper storage</strong> - Lock when not in use</li>
                    <li><strong>Report issues</strong> - Don't wait for problems to worsen</li>
                    <li><strong>No modifications</strong> - Don't install unauthorized software</li>
                </ul>
            </div>
        </div>
        
        {{-- Quick Actions --}}
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
            </div>
            <div class="box-body">
                <a href="{{ url('/tickets/create') }}" class="btn btn-warning btn-block" style="margin-bottom: 10px;">
                    <i class="fa fa-ticket"></i> Create Support Ticket
                </a>
                <a href="{{ url('/assets') }}" class="btn btn-default btn-block" style="margin-bottom: 10px;">
                    <i class="fa fa-list"></i> View All Assets
                </a>
                @if($assets->count() > 0)
                <button type="button" class="btn btn-success btn-block" onclick="reportAllGood()" style="margin-bottom: 10px;">
                    <i class="fa fa-check-circle"></i> Report All as Good
                </button>
                @endif
            </div>
        </div>
        
    </div>
</div>

{{-- Report Issue Modal --}}
<div class="modal fade" id="reportIssueModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-exclamation-triangle"></i> Report Asset Issue
                </h4>
            </div>
            <div class="modal-body">
                <form id="reportIssueForm">
                    <input type="hidden" id="issueAssetId" name="asset_id">
                    
                    <div class="form-group">
                        <label>Asset Tag:</label>
                        <input type="text" class="form-control" id="issueAssetTag" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Issue Type: <span class="text-danger">*</span></label>
                        <select class="form-control" name="issue_type" required>
                            <option value="">Select issue type...</option>
                            <option value="Hardware Malfunction">Hardware Malfunction</option>
                            <option value="Software Problem">Software Problem</option>
                            <option value="Physical Damage">Physical Damage</option>
                            <option value="Performance Issue">Performance Issue</option>
                            <option value="Missing Parts">Missing Parts</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Description: <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" rows="4" required 
                                  placeholder="Please describe the issue in detail..."></textarea>
                        <span class="help-block">Minimum 20 characters</span>
                    </div>
                    
                    <div class="form-group">
                        <label>Urgency:</label>
                        <select class="form-control" name="urgency">
                            <option value="Low">Low - Can work around it</option>
                            <option value="Medium" selected>Medium - Impacting productivity</option>
                            <option value="High">High - Cannot work</option>
                            <option value="Critical">Critical - System down</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitIssueReport()">
                    <i class="fa fa-paper-plane"></i> Submit Report
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable with export buttons
    var table = $('#myAssetsTable').DataTable({
        responsive: true,
        order: [[5, "desc"]], // Sort by assigned date descending
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fa fa-file-excel-o"></i> Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6] // Exclude actions column
                }
            },
            {
                extend: 'csvHtml5',
                text: '<i class="fa fa-file-text-o"></i> CSV',
                className: 'btn btn-primary btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6]
                }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fa fa-file-pdf-o"></i> PDF',
                className: 'btn btn-danger btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6]
                },
                orientation: 'landscape'
            },
            {
                extend: 'print',
                text: '<i class="fa fa-print"></i> Print',
                className: 'btn btn-default btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6]
                }
            }
        ],
        language: {
            emptyTable: "No assets assigned to you",
            info: "Showing _START_ to _END_ of _TOTAL_ assets",
            infoEmpty: "No assets to display",
            infoFiltered: "(filtered from _MAX_ total assets)",
            search: "Search assets:",
            paginate: {
                first: '<i class="fa fa-angle-double-left"></i>',
                previous: '<i class="fa fa-angle-left"></i>',
                next: '<i class="fa fa-angle-right"></i>',
                last: '<i class="fa fa-angle-double-right"></i>'
            }
        }
    });
    
    // Handle condition change
    $('.condition-select').on('change', function() {
        var assetId = $(this).data('asset-id');
        var condition = $(this).val();
        var selectElement = $(this);
        
        // AJAX to update condition
        $.ajax({
            url: '/assets/' + assetId + '/update-condition',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                condition: condition
            },
            success: function(response) {
                if (response.success) {
                    // Show success notification
                    toastr.success('Asset condition updated to: ' + condition);
                    
                    // Update row styling based on condition
                    var row = selectElement.closest('tr');
                    row.removeClass('warning danger success');
                    if (condition === 'Poor' || condition === 'Needs Attention') {
                        row.addClass('danger');
                    } else if (condition === 'Fair') {
                        row.addClass('warning');
                    } else {
                        row.addClass('success');
                    }
                } else {
                    toastr.error('Failed to update condition: ' + (response.message || 'Unknown error'));
                    // Revert selection
                    selectElement.val(selectElement.find('option:selected').data('original'));
                }
            },
            error: function(xhr) {
                toastr.error('Error updating condition. Please try again.');
                console.error('Error:', xhr);
            }
        });
    });
    
    // Acknowledge responsibility checkbox
    $('#acknowledgeResponsibility').on('change', function() {
        if ($(this).is(':checked')) {
            toastr.success('Thank you for acknowledging your asset responsibility!');
        }
    });
});

// Export function
window.exportMyAssets = function() {
    $('#myAssetsTable').DataTable().button('.buttons-excel').trigger();
};

// Report issue modal
window.reportIssue = function(assetId, assetTag) {
    $('#issueAssetId').val(assetId);
    $('#issueAssetTag').val(assetTag);
    $('#reportIssueForm')[0].reset();
    $('#issueAssetId').val(assetId); // Re-set after reset
    $('#issueAssetTag').val(assetTag);
    $('#reportIssueModal').modal('show');
};

// Submit issue report
window.submitIssueReport = function() {
    var form = $('#reportIssueForm');
    var description = form.find('[name="description"]').val();
    
    // Validate description length
    if (description.length < 20) {
        toastr.error('Description must be at least 20 characters long');
        return;
    }
    
    var assetId = $('#issueAssetId').val();
    var issueType = form.find('[name="issue_type"]').val();
    var urgency = form.find('[name="urgency"]').val();
    
    if (!issueType) {
        toastr.error('Please select an issue type');
        return;
    }
    
    // Create ticket with issue details
    var ticketSubject = issueType + ' - Asset #' + $('#issueAssetTag').val();
    var ticketDescription = 'Issue reported by asset holder:\n\n' +
                           'Issue Type: ' + issueType + '\n' +
                           'Urgency: ' + urgency + '\n\n' +
                           'Description:\n' + description;
    
    // Redirect to ticket creation with pre-filled data
    var url = '/tickets/create?asset_id=' + assetId + 
              '&subject=' + encodeURIComponent(ticketSubject) +
              '&description=' + encodeURIComponent(ticketDescription) +
              '&priority=' + urgency;
    
    window.location.href = url;
};

// Report all assets as good
window.reportAllGood = function() {
    if (!confirm('Are you sure you want to mark all your assets as in good condition?')) {
        return;
    }
    
    $.ajax({
        url: '/assets/my-assets/update-all-conditions',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            condition: 'Good'
        },
        success: function(response) {
            if (response.success) {
                toastr.success('All assets marked as Good condition!');
                // Update all dropdowns
                $('.condition-select').val('Good');
                // Remove warning/danger classes
                $('#myAssetsTable tbody tr').removeClass('warning danger').addClass('success');
            } else {
                toastr.error('Failed to update conditions: ' + (response.message || 'Unknown error'));
            }
        },
        error: function(xhr) {
            toastr.error('Error updating conditions. Please try again.');
            console.error('Error:', xhr);
        }
    });
};
</script>
@endsection
