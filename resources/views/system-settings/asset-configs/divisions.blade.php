@extends('layouts.app')

@section('page_title')
    Divisions Management
@endsection

@section('page_description')
    Manage organizational divisions and departments
@endsection

@section('main-content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-building"></i> Divisions
                </h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addDivisionModal">
                        <i class="fa fa-plus"></i> Add Division
                    </button>
                    <a href="{{ route('system-settings.index') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to Settings
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table id="divisionsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Division Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($divisions as $division)
                            <tr>
                                <td>{{ $division->id }}</td>
                                <td>
                                    <span class="badge badge-warning">
                                        {{ $division->name }}
                                    </span>
                                </td>
                                <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-sm btn-primary btn-edit-division" 
                                                data-id="{{ $division->id }}"
                                                data-name="{{ $division->name }}"
                                                data-toggle="modal" data-target="#editDivisionModal"
                                                title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteDivision({{ $division->id }})" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-division-{{ $division->id }}" method="POST" action="{{ route('divisions.destroy', $division->id) }}" style="display:none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">
                                    <p class="text-muted">No divisions found. <a href="#" data-toggle="modal" data-target="#addDivisionModal">Create your first one</a>.</p>
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

<script>
window.deleteDivision = function(id) {
    if(confirm('Are you sure you want to delete this division?')) {
        document.getElementById('delete-division-' + id).submit();
    }
};

$(document).ready(function() {
    $('#divisionsTable').DataTable({
        columnDefs: [{
            orderable: false, targets: 2 // Actions column
        }],
        order: [[ 0, "asc" ]]
    });

    // Handle edit division modal
    $('.btn-edit-division').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');

        $('#edit_name').val(name || '');
        $('#editDivisionForm').attr('action', '{{ route("divisions.update", ":division") }}'.replace(':division', id));
    });
});
</script>

<!-- Add Division Modal -->
<div class="modal fade" id="addDivisionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('divisions.store') }}">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Add New Division</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Division Name <span class="text-red">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required 
                               placeholder="e.g., IT Department, Human Resources, Finance">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Division</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Division Modal -->
<div class="modal fade" id="editDivisionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="editDivisionForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Edit Division</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_name">Division Name <span class="text-red">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Division</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

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