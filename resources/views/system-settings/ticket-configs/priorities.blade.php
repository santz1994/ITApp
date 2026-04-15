@extends('layouts.app')

@section('page_title')
    Ticket Priorities Management
@endsection

@section('page_description')
    Manage ticket priority levels and ordering
@endsection

@section('main-content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-exclamation-triangle"></i> Ticket Priorities
                </h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addPriorityModal">
                        <i class="fa fa-plus"></i> Add Priority
                    </button>
                    <a href="{{ route('system-settings.index') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to Settings
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="callout callout-info">
                    <h4><i class="fa fa-info-circle"></i> Priority Ordering</h4>
                    Priorities are automatically ordered logically: Urgent → High → Normal → Low
                </div>
                <div class="table-responsive">
                    <table id="prioritiesTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Priority Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($priorities as $priority)
                            <tr>
                                <td>{{ $priority->id }}</td>
                                <td>
                                    <span class="badge badge-warning">
                                        {{ $priority->name }}
                                    </span>
                                </td>
                                <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-sm btn-primary btn-edit-priority" 
                                                data-id="{{ $priority->id }}"
                                                data-name="{{ $priority->name }}"
                                                data-update-url="{{ route('tickets-priority.update', $priority->id) }}"
                                                data-toggle="modal" data-target="#editPriorityModal"
                                                title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deletePriority({{ $priority->id }})" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-priority-{{ $priority->id }}" method="POST" action="{{ route('tickets-priority.destroy', $priority->id) }}" style="display:none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">
                                    <p class="text-muted">No ticket priorities found. <a href="#" data-toggle="modal" data-target="#addPriorityModal">Create your first one</a>.</p>
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

<!-- Add Priority Modal -->
<div class="modal fade" id="addPriorityModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('tickets-priority.store') }}">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Add New Priority</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="priority">Priority Name <span class="text-red">*</span></label>
                        <input type="text" class="form-control" id="priority" name="priority" required 
                               placeholder="e.g., Urgent, High, Normal, Low">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Priority</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Priority Modal -->
<div class="modal fade" id="editPriorityModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="editPriorityForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Edit Priority</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_priority">Priority Name <span class="text-red">*</span></label>
                        <input type="text" class="form-control" id="edit_priority" name="priority" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Priority</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
window.deletePriority = function(id) {
    if(confirm('Are you sure you want to delete this priority?')) {
        document.getElementById('delete-priority-' + id).submit();
    }
};

$(document).ready(function() {
    // Initialize DataTable
    $('#prioritiesTable').DataTable({
        columnDefs: [{
            orderable: false, targets: 2 // Actions column
        }],
        order: [[ 0, "asc" ]]
    });

    // Handle edit priority modal
    $('.btn-edit-priority').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var updateUrl = $(this).data('update-url');

        $('#edit_priority').val(name);
        $('#editPriorityForm').attr('action', updateUrl);
    });
});
</script>
@endsection