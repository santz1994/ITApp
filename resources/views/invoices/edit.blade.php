@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Edit Invoice',
    'subtitle' => $invoice->invoice_number,
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Invoices', 'url' => url('invoices')],
        ['label' => 'Edit']
    ]
])

<div class="container-fluid">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="icon fa fa-warning"></i> Validation Errors</h4>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Invoice Metadata --}}
    <div class="alert alert-info metadata-alert">
        <div class="row">
            <div class="col-md-3">
                <strong><i class="fa fa-hashtag"></i> Invoice ID:</strong> #{{ $invoice->id }}
            </div>
            <div class="col-md-3">
                <strong><i class="fa fa-calendar"></i> Created:</strong> 
                {{ $invoice->created_at ? $invoice->created_at->format('M d, Y') : 'N/A' }}
            </div>
            <div class="col-md-3">
                <strong><i class="fa fa-clock"></i> Last Updated:</strong> 
                {{ $invoice->updated_at ? $invoice->updated_at->format('M d, Y') : 'N/A' }}
            </div>
            <div class="col-md-3">
                <strong><i class="fa fa-file-pdf"></i> PDF:</strong> 
                @if(Storage::exists('invoices/' . $invoice->invoice_number . '.pdf'))
                    <span class="text-success">Available</span>
                @else
                    <span class="text-muted">Not uploaded</span>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Main Form --}}
        <div class="col-md-8">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-edit"></i> Edit Invoice Details</h3>
                </div>
                <div class="box-body">
                    <form method="POST" action="{{ url('invoices/' . $invoice->id) }}" id="editInvoiceForm" enctype="multipart/form-data">
                        @method('PATCH')
                        @csrf

                        <fieldset>
                            <legend>
                                <span class="form-section-icon"><i class="fa fa-file-invoice"></i></span>
                                Invoice Information
                            </legend>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {{ hasErrorForClass($errors, 'invoice_number') }}">
                                        <label for="invoice_number">
                                            <i class="fa fa-hashtag"></i> Invoice Number <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               name="invoice_number" 
                                               id="invoice_number" 
                                               class="form-control" 
                                               value="{{ $invoice->invoice_number }}"
                                               placeholder="e.g., INV-2025-001"
                                               required>
                                        <small class="help-text">Unique invoice identifier</small>
                                        {{ hasErrorForField($errors, 'invoice_number') }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group {{ hasErrorForClass($errors, 'order_number') }}">
                                        <label for="order_number">
                                            <i class="fa fa-receipt"></i> Order/PO Number <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               name="order_number" 
                                               id="order_number" 
                                               class="form-control" 
                                               value="{{ $invoice->order_number }}"
                                               placeholder="e.g., PO-2025-001"
                                               required>
                                        <small class="help-text">Purchase order reference</small>
                                        {{ hasErrorForField($errors, 'order_number') }}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {{ hasErrorForClass($errors, 'invoiced_date') }}">
                                        <label for="invoiced_date">
                                            <i class="fa fa-calendar"></i> Invoice Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" 
                                               name="invoiced_date" 
                                               id="invoiced_date" 
                                               class="form-control" 
                                               value="{{ $invoice->invoiced_date }}"
                                               required>
                                        <small class="help-text">Date invoice was issued</small>
                                        {{ hasErrorForField($errors, 'invoiced_date') }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group {{ hasErrorForClass($errors, 'total') }}">
                                        <label for="total">
                                            <i class="fa fa-money-bill-wave"></i> Total (Incl. VAT) <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-addon">Rp</div>
                                            <input type="number" 
                                                   name="total" 
                                                   id="total" 
                                                   class="form-control" 
                                                   value="{{ $invoice->total }}"
                                                   step="0.01"
                                                   min="0"
                                                   required>
                                        </div>
                                        <small class="help-text">
                                            Total invoice amount including VAT. Current: <strong>Rp {{ number_format($invoice->total, 2) }}</strong>
                                        </small>
                                        {{ hasErrorForField($errors, 'total') }}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {{ hasErrorForClass($errors, 'division_id') }}">
                                        <label for="division_id">
                                            <i class="fa fa-sitemap"></i> Division <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control division_id" name="division_id" id="division_id" required>
                                            @foreach($divisions as $division)
                                                <option value="{{ $division->id }}" {{ $invoice->division_id == $division->id ? 'selected' : '' }}>
                                                    {{ $division->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="help-text">Assign to division/department</small>
                                        {{ hasErrorForField($errors, 'division_id') }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group {{ hasErrorForClass($errors, 'supplier_id') }}">
                                        <label for="supplier_id">
                                            <i class="fa fa-truck"></i> Supplier <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control supplier_id" name="supplier_id" id="supplier_id" required>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}" {{ $invoice->supplier_id == $supplier->id ? 'selected' : '' }}>
                                                    {{ $supplier->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="help-text">Select vendor/supplier</small>
                                        {{ hasErrorForField($errors, 'supplier_id') }}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {{ hasErrorForClass($errors, 'file') }}">
                                        <label for="file">
                                            <i class="fa fa-file-pdf"></i> Upload Invoice (PDF)
                                        </label>
                                        <input type="file" 
                                               name="file" 
                                               id="file" 
                                               class="form-control"
                                               accept=".pdf">
                                        <small class="help-text">
                                            PDF format only. Leave empty to keep existing file.
                                            @if(Storage::exists('invoices/' . $invoice->invoice_number . '.pdf'))
                                                <strong class="text-success">Current: PDF available</strong>
                                            @endif
                                        </small>
                                        {{ hasErrorForField($errors, 'file') }}
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <div class="form-group" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-warning btn-lg btn-gradient">
                                <i class="fa fa-save"></i> Update Invoice
                            </button>
                            <a href="{{ url('invoices') }}" class="btn btn-default btn-lg">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                            @if(Storage::exists('invoices/' . $invoice->invoice_number . '.pdf'))
                                <a href="{{ url('invoices/' . $invoice->id) }}" target="_blank" class="btn btn-success btn-lg">
                                    <i class="fa fa-file-pdf"></i> View PDF
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-md-4">
            {{-- Edit Tips --}}
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-lightbulb"></i> Edit Tips</h3>
                </div>
                <div class="box-body">
                    <p style="font-size: 13px; margin-bottom: 10px;"><strong><i class="fa fa-exclamation-triangle text-warning"></i> Important Notes:</strong></p>
                    <ul style="margin-left: 20px; font-size: 12px;">
                        <li><strong>Invoice Number:</strong> Changing will rename the PDF file</li>
                        <li><strong>Division Change:</strong> May affect budget tracking reports</li>
                        <li><strong>Amount Change:</strong> Update if corrections needed</li>
                        <li><strong>PDF Upload:</strong> Replaces existing file if uploaded</li>
                    </ul>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="box-body">
                    <a href="{{ url('invoices') }}" class="btn btn-default btn-block">
                        <i class="fa fa-arrow-left"></i> Back to Invoices
                    </a>
                    @if(Storage::exists('invoices/' . $invoice->invoice_number . '.pdf'))
                        <a href="{{ url('invoices/' . $invoice->id) }}" target="_blank" class="btn btn-success btn-block">
                            <i class="fa fa-file-pdf"></i> View PDF Invoice
                        </a>
                    @endif
                    <hr>
                    <form method="POST" action="{{ url('invoices/' . $invoice->id) }}" onsubmit="return confirm('Are you sure you want to delete this invoice? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fa fa-trash"></i> Delete Invoice
                        </button>
                    </form>
                </div>
            </div>

            {{-- Invoice Summary --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Invoice Summary</h3>
                </div>
                <div class="box-body">
                    <table class="table table-condensed" style="font-size: 12px; margin-bottom: 0;">
                        <tr>
                            <td><strong><i class="fa fa-hashtag"></i> Invoice #:</strong></td>
                            <td>{{ $invoice->invoice_number }}</td>
                        </tr>
                        <tr>
                            <td><strong><i class="fa fa-receipt"></i> Order #:</strong></td>
                            <td>{{ $invoice->order_number }}</td>
                        </tr>
                        <tr>
                            <td><strong><i class="fa fa-truck"></i> Supplier:</strong></td>
                            <td>{{ $invoice->supplier->name }}</td>
                        </tr>
                        <tr>
                            <td><strong><i class="fa fa-sitemap"></i> Division:</strong></td>
                            <td>{{ $invoice->division->name }}</td>
                        </tr>
                        <tr>
                            <td><strong><i class="fa fa-money-bill-wave"></i> Amount:</strong></td>
                            <td><strong style="color: #28a745;">Rp {{ number_format($invoice->total, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td><strong><i class="fa fa-calendar"></i> Date:</strong></td>
                            <td>{{ \Carbon\Carbon::parse($invoice->invoiced_date)->format('M d, Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Related Assets --}}
            @php
                $relatedAssets = \App\Asset::where('invoice_id', $invoice->id)->get();
            @endphp
            @if($relatedAssets->count() > 0)
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-laptop"></i> Related Assets</h3>
                    </div>
                    <div class="box-body">
                        <p style="font-size: 12px; margin-bottom: 10px;">
                            <strong>{{ $relatedAssets->count() }}</strong> asset(s) linked to this invoice:
                        </p>
                        <ul style="margin-left: 20px; font-size: 12px; max-height: 200px; overflow-y: auto;">
                            @foreach($relatedAssets as $asset)
                                <li>
                                    <a href="{{ url('assets/' . $asset->id . '/edit') }}">
                                        {{ $asset->asset_tag }} - {{ $asset->model->name ?? 'N/A' }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2
        $(".supplier_id, .division_id").select2({
            placeholder: "-- Select --"
        });

        // Form validation
        $('#editInvoiceForm').on('submit', function(e) {
            var invoiceNum = $('#invoice_number').val();
            var orderNum = $('#order_number').val();
            var total = $('#total').val();
            var division = $('#division_id').val();
            var supplier = $('#supplier_id').val();

            if (!invoiceNum || !orderNum || !total || !division || !supplier) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }

            if (parseFloat(total) <= 0) {
                e.preventDefault();
                alert('Invoice total must be greater than 0.');
                return false;
            }
        });

        // Auto-dismiss alerts
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });
</script>
@endpush


