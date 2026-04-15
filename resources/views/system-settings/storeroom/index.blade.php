@extends('layouts.app')

@section('page_title')
    Storeroom Management
@endsection

@section('page_description')
    Manage storeroom items, parts, and inventory
@endsection

@section('main-content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-archive"></i> Storeroom Items
                </h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addItemModal">
                        <i class="fa fa-plus"></i> Add Item
                    </button>
                    <a href="{{ route('system-settings.index') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to Settings
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-blue"><i class="fa fa-cube"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Items</span>
                                <span class="info-box-number">{{ $totalItems ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">In Stock</span>
                                <span class="info-box-number">{{ $inStockItems ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-yellow"><i class="fa fa-exclamation-triangle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Low Stock</span>
                                <span class="info-box-number">{{ $lowStockItems ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-red"><i class="fa fa-times"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Out of Stock</span>
                                <span class="info-box-number">{{ $outOfStockItems ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="storeroomTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>SKU/Part Number</th>
                                <th>Current Stock</th>
                                <th>Min Stock</th>
                                <th>Status</th>
                                <th>Unit Price</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($storeroomItems as $item)
                            <tr class="{{ $item->quantity <= $item->min_quantity ? 'danger' : ($item->quantity <= $item->min_quantity * 2 ? 'warning' : '') }}">
                                <td>{{ $item->id }}</td>
                                <td>
                                    <strong>{{ $item->name }}</strong>
                                    @if($item->description)
                                        <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($item->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="label label-info">{{ $item->category ?? 'General' }}</span>
                                </td>
                                <td>{{ $item->sku ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $item->quantity > $item->min_quantity ? 'green' : ($item->quantity > 0 ? 'yellow' : 'red') }}">
                                        {{ $item->quantity }} {{ $item->unit ?? 'pcs' }}
                                    </span>
                                </td>
                                <td>{{ $item->min_quantity }} {{ $item->unit ?? 'pcs' }}</td>
                                <td>
                                    @if($item->quantity <= 0)
                                        <span class="label label-danger">Out of Stock</span>
                                    @elseif($item->quantity <= $item->min_quantity)
                                        <span class="label label-warning">Low Stock</span>
                                    @else
                                        <span class="label label-success">In Stock</span>
                                    @endif
                                </td>
                                <td>{{ $item->unit_price ? '$' . number_format($item->unit_price, 2) : '-' }}</td>
                                <td>{{ $item->updated_at->format('M d, Y') }}</td>
                                <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-sm btn-primary btn-edit-item" 
                                                data-id="{{ $item->id }}"
                                                data-name="{{ $item->name }}"
                                                data-description="{{ $item->description }}"
                                                data-category="{{ $item->category }}"
                                                data-sku="{{ $item->sku }}"
                                                data-quantity="{{ $item->quantity }}"
                                                data-min-quantity="{{ $item->min_quantity }}"
                                                data-unit="{{ $item->unit }}"
                                                data-price="{{ $item->unit_price }}"
                                                data-toggle="modal" data-target="#editItemModal"
                                                title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success btn-adjust-stock" 
                                                data-id="{{ $item->id }}"
                                                data-name="{{ $item->name }}"
                                                data-current="{{ $item->quantity }}"
                                                data-unit="{{ $item->unit }}"
                                                data-toggle="modal" data-target="#adjustStockModal"
                                                title="Adjust Stock">
                                            <i class="fa fa-plus-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteStoreroomItem({{ $item->id }})" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-storeroom-item-{{ $item->id }}" method="POST" action="#" style="display:none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">
                                    <p class="text-muted">No storeroom items found. <a href="#" data-toggle="modal" data-target="#addItemModal">Add your first item</a>.</p>
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

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="#">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Add New Storeroom Item</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Item Name <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category">Category</label>
                                <select class="form-control" id="category" name="category">
                                    <option value="General">General</option>
                                    <option value="Hardware">Hardware</option>
                                    <option value="Software">Software</option>
                                    <option value="Consumables">Consumables</option>
                                    <option value="Cables">Cables</option>
                                    <option value="Parts">Parts</option>
                                    <option value="Tools">Tools</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sku">SKU/Part Number</label>
                                <input type="text" class="form-control" id="sku" name="sku">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit">Unit</label>
                                <select class="form-control" id="unit" name="unit">
                                    <option value="pcs">Pieces</option>
                                    <option value="box">Box</option>
                                    <option value="roll">Roll</option>
                                    <option value="meter">Meter</option>
                                    <option value="kg">Kilogram</option>
                                    <option value="liter">Liter</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="quantity">Initial Quantity <span class="text-red">*</span></label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="min_quantity">Minimum Stock Level <span class="text-red">*</span></label>
                                <input type="number" class="form-control" id="min_quantity" name="min_quantity" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="unit_price">Unit Price</label>
                                <input type="number" class="form-control" id="unit_price" name="unit_price" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" id="editItemForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Edit Storeroom Item</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_name">Item Name <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_category">Category</label>
                                <select class="form-control" id="edit_category" name="category">
                                    <option value="General">General</option>
                                    <option value="Hardware">Hardware</option>
                                    <option value="Software">Software</option>
                                    <option value="Consumables">Consumables</option>
                                    <option value="Cables">Cables</option>
                                    <option value="Parts">Parts</option>
                                    <option value="Tools">Tools</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_sku">SKU/Part Number</label>
                                <input type="text" class="form-control" id="edit_sku" name="sku">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_unit">Unit</label>
                                <select class="form-control" id="edit_unit" name="unit">
                                    <option value="pcs">Pieces</option>
                                    <option value="box">Box</option>
                                    <option value="roll">Roll</option>
                                    <option value="meter">Meter</option>
                                    <option value="kg">Kilogram</option>
                                    <option value="liter">Liter</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_min_quantity">Minimum Stock Level <span class="text-red">*</span></label>
                                <input type="number" class="form-control" id="edit_min_quantity" name="min_quantity" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_unit_price">Unit Price</label>
                                <input type="number" class="form-control" id="edit_unit_price" name="unit_price" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Adjust Stock Modal -->
<div class="modal fade" id="adjustStockModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="adjustStockForm">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Adjust Stock Level</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h4><i class="fa fa-info-circle"></i> <span id="stock-item-name"></span></h4>
                        <p>Current Stock: <strong><span id="current-stock"></span></strong></p>
                    </div>
                    <div class="form-group">
                        <label for="adjustment_type">Adjustment Type</label>
                        <select class="form-control" id="adjustment_type" name="adjustment_type">
                            <option value="add">Add Stock</option>
                            <option value="remove">Remove Stock</option>
                            <option value="set">Set Stock Level</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="adjustment_quantity">Quantity</label>
                        <input type="number" class="form-control" id="adjustment_quantity" name="adjustment_quantity" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="adjustment_reason">Reason</label>
                        <textarea class="form-control" id="adjustment_reason" name="adjustment_reason" rows="3" 
                                  placeholder="Enter reason for stock adjustment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Adjust Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
window.deleteStoreroomItem = function(id) {
    if(confirm('Are you sure you want to delete this item?')) {
        document.getElementById('delete-storeroom-item-' + id).submit();
    }
};

$(document).ready(function() {
    // Initialize DataTable
    $('#storeroomTable').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "pageLength": 25,
        "order": [[ 0, "desc" ]]
    });

    // Handle edit item modal
    $('.btn-edit-item').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var description = $(this).data('description');
        var category = $(this).data('category');
        var sku = $(this).data('sku');
        var quantity = $(this).data('quantity');
        var minQuantity = $(this).data('min-quantity');
        var unit = $(this).data('unit');
        var price = $(this).data('price');

        $('#edit_name').val(name);
        $('#edit_description').val(description);
        $('#edit_category').val(category);
        $('#edit_sku').val(sku);
        $('#edit_min_quantity').val(minQuantity);
        $('#edit_unit').val(unit);
        $('#edit_unit_price').val(price);
        
        $('#editItemForm').attr('action', '/storeroom/' + id);
    });

    // Handle adjust stock modal
    $('.btn-adjust-stock').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var current = $(this).data('current');
        var unit = $(this).data('unit');

        $('#stock-item-name').text(name);
        $('#current-stock').text(current + ' ' + unit);
        
        $('#adjustStockForm').attr('action', '/storeroom/' + id + '/adjust-stock');
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