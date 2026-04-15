@extends('layouts.app')

@section('page_title')
    Suppliers Management
@endsection

@section('page_description')
    Manage vendor and supplier information
@endsection

@section('main-content')
<div class="row">
    <div class="col-md-12">
        
        <!-- Quick Stats -->
        <div class="row">
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-blue"><i class="fa fa-truck"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Suppliers</span>
                        <span class="info-box-number">{{ $suppliers->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-truck"></i> Suppliers
                </h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addSupplierModal">
                        <i class="fa fa-plus"></i> Add Supplier
                    </button>
                    <a href="{{ route('system-settings.index') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to Settings
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table id="suppliersTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Supplier Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($suppliers as $supplier)
                            <tr>
                                <td>{{ $supplier->id }}</td>
                                <td>
                                    <span class="badge badge-success">
                                        {{ $supplier->name }}
                                    </span>
                                </td>
                                <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-sm btn-primary btn-edit-supplier" 
                                                data-id="{{ $supplier->id }}"
                                                data-name="{{ $supplier->name }}"
                                                data-toggle="modal" data-target="#editSupplierModal"
                                                title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteSupplier({{ $supplier->id }})" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-supplier-{{ $supplier->id }}" method="POST" action="{{ route('suppliers.destroy', $supplier->id) }}" style="display:none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">
                                    <p class="text-muted">No suppliers found. <a href="#" data-toggle="modal" data-target="#addSupplierModal">Add your first supplier</a>.</p>
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

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('suppliers.store') }}">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Add New Supplier</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Supplier Name <span class="text-red">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required 
                               placeholder="e.g., Dell Technologies, Microsoft, Local Vendor">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Supplier Modal -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" id="editSupplierForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Edit Supplier</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_name">Supplier Name <span class="text-red">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.deleteSupplier = function(id) {
    if(confirm('Are you sure you want to delete this supplier?')) {
        document.getElementById('delete-supplier-' + id).submit();
    }
};

$(document).ready(function() {
    $('#suppliersTable').DataTable({
        columnDefs: [{
            orderable: false, targets: 2 // Actions column
        }],
        order: [[ 0, "asc" ]]
    });

    // Handle edit supplier modal
    $('.btn-edit-supplier').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');

        $('#edit_name').val(name || '');
        $('#editSupplierForm').attr('action', '{{ route("suppliers.update", ":supplier") }}'.replace(':supplier', id));
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