@extends('layouts.app')

@section('page_title')
    Warranty Types Management
@endsection

@section('page_description')
    Manage warranty categories and coverage options
@endsection

@section('main-content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-shield"></i> Warranty Types
                </h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addWarrantyModal">
                        <i class="fa fa-plus"></i> Add Warranty Type
                    </button>
                    <a href="{{ route('system-settings.index') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to Settings
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table id="warrantyTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Warranty Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($warrantyTypes as $warranty)
                            <tr>
                                <td>{{ $warranty->id }}</td>
                                <td>
                                    <span class="badge badge-secondary">
                                        {{ $warranty->name }}
                                    </span>
                                </td>
                                <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-sm btn-primary btn-edit-warranty" 
                                                data-id="{{ $warranty->id }}"
                                                data-name="{{ $warranty->name }}"
                                                data-toggle="modal" data-target="#editWarrantyModal"
                                                title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteWarrantyType({{ $warranty->id }})" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-warranty-type-{{ $warranty->id }}" method="POST" action="{{ route('warranty-types.destroy', $warranty->id) }}" style="display:none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">
                                    <p class="text-muted">No warranty types found. <a href="#" data-toggle="modal" data-target="#addWarrantyModal">Create your first one</a>.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Warranty Modal -->
<div class="modal fade" id="addWarrantyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('warranty-types.store') }}">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Add New Warranty Type</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Warranty Name <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required 
                                       placeholder="e.g., Standard Warranty, Extended Coverage">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="duration_months">Duration (Months)</label>
                                <input type="number" class="form-control" id="duration_months" name="duration_months" min="1" 
                                       placeholder="e.g., 12, 24, 36">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="coverage_type">Coverage Type</label>
                        <select class="form-control" id="coverage_type" name="coverage_type">
                            <option value="full">Full Coverage</option>
                            <option value="parts">Parts Only</option>
                            <option value="labor">Labor Only</option>
                            <option value="limited">Limited Coverage</option>
                            <option value="onsite">On-site Service</option>
                            <option value="depot">Depot Service</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" 
                                  placeholder="Describe what this warranty covers, exclusions, terms, etc."></textarea>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_default" value="1"> Set as default warranty type
                        </label>
                        <p class="help-block">Only one warranty type can be default at a time.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Warranty Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Warranty Modal -->
<div class="modal fade" id="editWarrantyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="editWarrantyForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Edit Warranty Type</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_name">Warranty Name <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_duration_months">Duration (Months)</label>
                                <input type="number" class="form-control" id="edit_duration_months" name="duration_months" min="1">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_coverage_type">Coverage Type</label>
                        <select class="form-control" id="edit_coverage_type" name="coverage_type">
                            <option value="full">Full Coverage</option>
                            <option value="parts">Parts Only</option>
                            <option value="labor">Labor Only</option>
                            <option value="limited">Limited Coverage</option>
                            <option value="onsite">On-site Service</option>
                            <option value="depot">Depot Service</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="edit_is_default" name="is_default" value="1"> Set as default warranty type
                        </label>
                        <p class="help-block">Only one warranty type can be default at a time.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Warranty Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.deleteWarrantyType = function(id) {
    if(confirm('Are you sure you want to delete this warranty type?')) {
        document.getElementById('delete-warranty-type-' + id).submit();
    }
};

$(document).ready(function() {
    $('#warrantyTable').DataTable({
        columnDefs: [{
            orderable: false, targets: 2 // Actions column
        }],
        order: [[ 0, "asc" ]]
    });

    // Handle edit warranty type modal
    $('.btn-edit-warranty').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');

        $('#edit_name').val(name || '');
        $('#editWarrantyForm').attr('action', '{{ route("warranty-types.update", ":warranty") }}'.replace(':warranty', id));
    });
});
</script>

@if(Session::has('status'))
    <div class="alert alert-success" style="margin-top:10px;">
        {{ Session::get('message') }}
    </div>
    <script>
        $(document).ready(function() {
            Command: toastr["{{Session::get('status')}}"]("{{Session::get('message')}}", "{{Session::get('title')}}");
        });
    </script>
@endif
@endsection