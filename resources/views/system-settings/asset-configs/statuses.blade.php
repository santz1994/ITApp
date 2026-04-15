@extends('layouts.app')

@section('page_title')
    Asset Statuses Management
@endsection

@section('page_description')
    Manage asset status labels and lifecycle stages
@endsection

@section('main-content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-info-circle"></i> Asset Statuses
                </h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addStatusModal">
                        <i class="fa fa-plus"></i> Add Status
                    </button>
                    <a href="{{ route('system-settings.index') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to Settings
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table id="statusesTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($statuses as $status)
                            <tr>
                                <td>{{ $status->id }}</td>
                                <td>
                                    <span class="badge badge-primary">
                                        {{ $status->name }}
                                    </span>
                                </td>
                                <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-sm btn-primary btn-edit-status" 
                                                data-id="{{ $status->id }}"
                                                data-name="{{ $status->name }}"
                                                data-toggle="modal" data-target="#editStatusModal"
                                                title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteStatus({{ $status->id }})" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-status-{{ $status->id }}" method="POST" action="{{ route('status.destroy', $status->id) }}" style="display:none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">
                                    <p class="text-muted">No asset statuses found. <a href="#" data-toggle="modal" data-target="#addStatusModal">Create your first one</a>.</p>
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

<!-- Add Status Modal -->
<div class="modal fade" id="addStatusModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('status.store') }}">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Add New Asset Status</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Status Name <span class="text-red">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required 
                               placeholder="e.g., Active, In Repair, Disposed, Available">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Status Modal -->
<div class="modal fade" id="editStatusModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="editStatusForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Edit Asset Status</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_name">Status Name <span class="text-red">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.deleteStatus = function(id) {
    if(confirm('Are you sure you want to delete this status?')) {
        document.getElementById('delete-status-' + id).submit();
    }
};

$(document).ready(function() {
    $('#statusesTable').DataTable({
        columnDefs: [{
            orderable: false, targets: 2 // Actions column
        }],
        order: [[ 0, "asc" ]]
    });

    // Handle edit status modal
    $('.btn-edit-status').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');

        $('#edit_name').val(name);
        $('#editStatusForm').attr('action', '{{ route("status.update", ":status") }}'.replace(':status', id));
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