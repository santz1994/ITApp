@extends('layouts.app')

@section('page_title')
    Canned Fields Management
@endsection

@section('page_description')
    Manage predefined ticket response templates
@endsection

@section('main-content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-text-width"></i> Canned Fields
                </h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addCannedFieldModal">
                        <i class="fa fa-plus"></i> Add Canned Field
                    </button>
                    <a href="{{ route('system-settings.index') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to Settings
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table id="cannedFieldsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Content</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cannedFields as $field)
                            <tr>
                                <td>{{ $field->id }}</td>
                                <td>{{ $field->title }}</td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="{{ $field->content }}">
                                        {{ \Illuminate\Support\Str::limit($field->content, 50) }}
                                    </div>
                                </td>
                                <td>
                                    <span class="label label-info">{{ $field->category ?? 'General' }}</span>
                                </td>
                                <td>
                                    <span class="label label-{{ $field->is_active ? 'success' : 'danger' }}">
                                        {{ $field->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ $field->created_at->format('M d, Y') }}</td>
                                <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-sm btn-primary btn-edit-canned-field" 
                                                data-id="{{ $field->id }}"
                                                data-title="{{ $field->title }}"
                                                data-content="{{ $field->content }}"
                                                data-category="{{ $field->category }}"
                                                data-active="{{ $field->is_active }}"
                                                data-toggle="modal" data-target="#editCannedFieldModal"
                                                title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteCannedField({{ $field->id }})" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-canned-field-{{ $field->id }}" method="POST" action="{{ route('tickets-canned-field.destroy', $field->id) }}" style="display:none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    <p class="text-muted">No canned fields found. <a href="#" data-toggle="modal" data-target="#addCannedFieldModal">Create your first one</a>.</p>
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

<!-- Add Canned Field Modal -->
<div class="modal fade" id="addCannedFieldModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('tickets-canned-field.store') }}">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Add New Canned Field</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title">Title <span class="text-red">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="content">Content <span class="text-red">*</span></label>
                        <textarea class="form-control" id="content" name="content" rows="5" required 
                                  placeholder="Enter the template content..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select class="form-control" id="category" name="category">
                            <option value="General">General</option>
                            <option value="Hardware">Hardware</option>
                            <option value="Software">Software</option>
                            <option value="Network">Network</option>
                            <option value="Account">Account</option>
                            <option value="Resolution">Resolution</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" value="1" checked> Active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Canned Field</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Canned Field Modal -->
<div class="modal fade" id="editCannedFieldModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="editCannedFieldForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Edit Canned Field</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_title">Title <span class="text-red">*</span></label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_content">Content <span class="text-red">*</span></label>
                        <textarea class="form-control" id="edit_content" name="content" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_category">Category</label>
                        <select class="form-control" id="edit_category" name="category">
                            <option value="General">General</option>
                            <option value="Hardware">Hardware</option>
                            <option value="Software">Software</option>
                            <option value="Network">Network</option>
                            <option value="Account">Account</option>
                            <option value="Resolution">Resolution</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="edit_is_active" name="is_active" value="1"> Active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Canned Field</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
window.deleteCannedField = function(id) {
    if(confirm('Are you sure you want to delete this canned field?')) {
        document.getElementById('delete-canned-field-' + id).submit();
    }
};

$(document).ready(function() {
    // Initialize DataTable
    $('#cannedFieldsTable').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "pageLength": 25,
        "order": [[ 0, "desc" ]]
    });

    // Handle edit canned field modal
    $('.btn-edit-canned-field').on('click', function() {
        var id = $(this).data('id');
        var title = $(this).data('title');
        var content = $(this).data('content');
        var category = $(this).data('category');
        var isActive = $(this).data('active');

        $('#edit_title').val(title);
        $('#edit_content').val(content);
        $('#edit_category').val(category);
        $('#edit_is_active').prop('checked', isActive == 1);
        
        $('#editCannedFieldForm').attr('action', '{{ url("/tickets-canned-field") }}/' + id);
    });

    // Auto-resize textareas
    $('textarea').each(function() {
        this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;');
    }).on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
});
</script>
@endsection