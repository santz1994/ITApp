@extends('layouts.app')

@section('page_title')
    Invoices Management
@endsection

@section('page_description')
    Manage invoice records and financial tracking
@endsection

@section('main-content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-file-text"></i> Invoices
                </h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addInvoiceModal">
                        <i class="fa fa-plus"></i> Add Invoice
                    </button>
                    <a href="{{ route('system-settings.index') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to Settings
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table id="invoicesTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Invoice Number</th>
                                <th>Order Number</th>
                                <th>Supplier</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                            <tr>
                                <td>{{ $invoice->id }}</td>
                                <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                <td>{{ $invoice->order_number ?? '-' }}</td>
                                <td>{{ $invoice->supplier->name ?? '-' }}</td>
                                <td>{{ $invoice->invoice_date ? $invoice->invoice_date->format('M d, Y') : '-' }}</td>
                                <td>
                                    @if($invoice->total)
                                        <strong>${{ number_format($invoice->total, 2) }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-sm btn-primary btn-edit-invoice" 
                                                data-id="{{ $invoice->id }}"
                                                data-number="{{ $invoice->invoice_number }}"
                                                data-po="{{ $invoice->po_number }}"
                                                data-supplier="{{ $invoice->supplier_id }}"
                                                data-date="{{ $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : '' }}"
                                                data-amount="{{ $invoice->total_amount }}"
                                                data-status="{{ $invoice->status }}"
                                                data-notes="{{ $invoice->notes }}"
                                                data-toggle="modal" data-target="#editInvoiceModal"
                                                title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-sm btn-success" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteInvoice({{ $invoice->id }})" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-invoice-{{ $invoice->id }}" method="POST" action="{{ route('invoices.destroy', $invoice->id) }}" style="display:none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    <p class="text-muted">No invoices found. <a href="#" data-toggle="modal" data-target="#addInvoiceModal">Add your first invoice</a>.</p>
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

<!-- Add Invoice Modal -->
<div class="modal fade" id="addInvoiceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('invoices.store') }}">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Add New Invoice</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="invoice_number">Invoice Number <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="invoice_number" name="invoice_number" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="po_number">PO Number</label>
                                <input type="text" class="form-control" id="po_number" name="po_number">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="supplier_id">Supplier <span class="text-red">*</span></label>
                                <select class="form-control" id="supplier_id" name="supplier_id" required>
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="invoice_date">Invoice Date <span class="text-red">*</span></label>
                                <input type="date" class="form-control" id="invoice_date" name="invoice_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="total_amount">Total Amount</label>
                                <input type="number" class="form-control" id="total_amount" name="total_amount" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                    <option value="overdue">Overdue</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Invoice Modal -->
<div class="modal fade" id="editInvoiceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" id="editInvoiceForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Edit Invoice</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_invoice_number">Invoice Number <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="edit_invoice_number" name="invoice_number" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_po_number">PO Number</label>
                                <input type="text" class="form-control" id="edit_po_number" name="po_number">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_supplier_id">Supplier <span class="text-red">*</span></label>
                                <select class="form-control" id="edit_supplier_id" name="supplier_id" required>
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_invoice_date">Invoice Date <span class="text-red">*</span></label>
                                <input type="date" class="form-control" id="edit_invoice_date" name="invoice_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_total_amount">Total Amount</label>
                                <input type="number" class="form-control" id="edit_total_amount" name="total_amount" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_status">Status</label>
                                <select class="form-control" id="edit_status" name="status">
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                    <option value="overdue">Overdue</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_notes">Notes</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.deleteInvoice = function(id) {
    if(confirm('Are you sure you want to delete this invoice?')) {
        document.getElementById('delete-invoice-' + id).submit();
    }
};

$(document).ready(function() {
    $('#invoicesTable').DataTable({
        columnDefs: [{
            // Actions column is the last column (index 6 - now 7 columns total, 0-indexed)
            orderable: false, targets: 6
        }],
        order: [[ 0, "asc" ]]
    });

    // Handle edit invoice modal
    $('.btn-edit-invoice').on('click', function() {
        var id = $(this).data('id');
        var number = $(this).data('number');
        var supplierId = $(this).data('supplier');
        var amount = $(this).data('amount');
        var date = $(this).data('date');
        var due = $(this).data('due');
        var description = $(this).data('description');

        $('#edit_invoice_number').val(number || '');
        $('#edit_supplier_id').val(supplierId || '');
        $('#edit_amount').val(amount || '');
        $('#edit_invoice_date').val(date || '');
        $('#edit_due_date').val(due || '');
        $('#edit_description').val(description || '');
        
        $('#editInvoiceForm').attr('action', '{{ route("invoices.update", ":invoice") }}'.replace(':invoice', id));
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