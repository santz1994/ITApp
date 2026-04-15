@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Invoice Management',
    'subtitle' => 'Financial Tracking & Documentation',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Invoices']
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

    @if(Session::has('status'))
        <div class="alert alert-{{ Session::get('status') == 'success' ? 'success' : 'danger' }} alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="fa fa-{{ Session::get('status') == 'success' ? 'check-circle' : 'exclamation-triangle' }}"></i>
            <strong>{{ Session::get('title') }}</strong> - {{ Session::get('message') }}
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

    <div class="row">
        {{-- Main Content --}}
        <div class="col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-file-invoice-dollar"></i> All Invoices
                        <span class="count-badge">{{ $invoices->count() }}</span>
                    </h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <table id="table" class="table table-bordered table-striped table-hover table-enhanced">
                        <thead>
                            <tr>
                                <th><i class="fa fa-hashtag"></i> Invoice #</th>
                                <th><i class="fa fa-receipt"></i> Order #</th>
                                <th><i class="fa fa-money-bill-wave"></i> Total (Incl. VAT)</th>
                                <th><i class="fa fa-sitemap"></i> Division</th>
                                <th><i class="fa fa-truck"></i> Supplier</th>
                                <th><i class="fa fa-calendar"></i> Invoice Date</th>
                                <th style="width: 150px;"><i class="fa fa-cog"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($invoices->count() > 0)
                                @foreach($invoices as $invoice)
                                    <tr>
                                        <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                        <td>{{ $invoice->order_number }}</td>
                                        <td>
                                            <strong style="color: #28a745; font-size: 14px;">
                                                Rp {{ number_format($invoice->total, 2) }}
                                            </strong>
                                        </td>
                                        <td>{{ $invoice->division->name }}</td>
                                        <td>{{ $invoice->supplier->name }}</td>
                                        <td>{{ $invoice->invoiced_date ? \Carbon\Carbon::parse($invoice->invoiced_date)->format('M d, Y') : 'N/A' }}</td>
                                        <td>
                                            <a href="{{ url('invoices/' . $invoice->id) }}" class="btn btn-sm btn-primary" title="View Details">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ url('invoices/' . $invoice->id . '/pdf') }}" class="btn btn-sm btn-success" title="View PDF" target="_blank">
                                                <i class="fa fa-file-pdf"></i>
                                            </a>
                                            <a href="{{ url('invoices/' . $invoice->id . '/edit') }}" class="btn btn-sm btn-primary" title="Edit Invoice">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7" class="text-center empty-state" style="padding: 30px;">
                                        <i class="fa fa-file-invoice-dollar fa-3x" style="opacity: 0.3; margin-bottom: 15px;"></i>
                                        <p>No invoices found.</p>
                                        <p class="text-muted" style="font-size: 12px;">Create your first invoice using the form on the right.</p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        @if($invoices->count() > 0)
                            <tfoot>
                                <tr style="background-color: #f9f9f9; font-weight: bold;">
                                    <td colspan="2" style="text-align: right;">Total:</td>
                                    <td>
                                        <strong style="color: #28a745; font-size: 16px;">
                                            Rp {{ number_format($invoices->sum('total'), 2) }}
                                        </strong>
                                    </td>
                                    <td colspan="4"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-md-3">
            {{-- Create Invoice Form --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-plus-circle"></i> Create Invoice</h3>
                </div>
                <div class="box-body">
                    <form method="POST" action="{{ url('invoices') }}" id="createInvoiceForm" enctype="multipart/form-data">
                        @csrf
                        
                        <fieldset>
                            <legend>
                                <span class="form-section-icon"><i class="fa fa-file-invoice"></i></span>
                                Invoice Details
                            </legend>

                            <div class="form-group {{ hasErrorForClass($errors, 'invoice_number') }}">
                                <label for="invoice_number">
                                    <i class="fa fa-hashtag"></i> Invoice Number <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="invoice_number" 
                                       id="invoice_number" 
                                       class="form-control" 
                                       value="{{ old('invoice_number') }}"
                                       placeholder="e.g., INV-2025-001"
                                       required>
                                <small class="help-text">Unique invoice identifier</small>
                                {{ hasErrorForField($errors, 'invoice_number') }}
                            </div>

                            <div class="form-group {{ hasErrorForClass($errors, 'order_number') }}">
                                <label for="order_number">
                                    <i class="fa fa-receipt"></i> Order/PO Number <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="order_number" 
                                       id="order_number" 
                                       class="form-control" 
                                       value="{{ old('order_number') }}"
                                       placeholder="e.g., PO-2025-001"
                                       required>
                                <small class="help-text">Purchase order reference</small>
                                {{ hasErrorForField($errors, 'order_number') }}
                            </div>

                            <div class="form-group {{ hasErrorForClass($errors, 'invoiced_date') }}">
                                <label for="invoiced_date">
                                    <i class="fa fa-calendar"></i> Invoice Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       name="invoiced_date" 
                                       id="invoiced_date" 
                                       class="form-control" 
                                       value="{{ old('invoiced_date', date('Y-m-d')) }}"
                                       required>
                                <small class="help-text">Date invoice was issued</small>
                                {{ hasErrorForField($errors, 'invoiced_date') }}
                            </div>

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
                                           value="{{ old('total') }}"
                                           step="0.01"
                                           min="0"
                                           placeholder="0.00"
                                           required>
                                </div>
                                <small class="help-text">Total invoice amount including VAT</small>
                                {{ hasErrorForField($errors, 'total') }}
                            </div>

                            <div class="form-group {{ hasErrorForClass($errors, 'division_id') }}">
                                <label for="division_id">
                                    <i class="fa fa-sitemap"></i> Division <span class="text-danger">*</span>
                                </label>
                                <select class="form-control division_id" name="division_id" id="division_id" required>
                                    <option value="">-- Select Division --</option>
                                    @foreach($divisions as $division)
                                        <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>
                                            {{ $division->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="help-text">Assign to division/department</small>
                                {{ hasErrorForField($errors, 'division_id') }}
                            </div>

                            <div class="form-group {{ hasErrorForClass($errors, 'supplier_id') }}">
                                <label for="supplier_id">
                                    <i class="fa fa-truck"></i> Supplier <span class="text-danger">*</span>
                                </label>
                                <select class="form-control supplier_id" name="supplier_id" id="supplier_id" required>
                                    <option value="">-- Select Supplier --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="help-text">Select vendor/supplier</small>
                                {{ hasErrorForField($errors, 'supplier_id') }}
                            </div>

                            <div class="form-group {{ hasErrorForClass($errors, 'file') }}">
                                <label for="file">
                                    <i class="fa fa-file-pdf"></i> Upload Invoice (PDF)
                                </label>
                                <input type="file" 
                                       name="file" 
                                       id="file" 
                                       class="form-control"
                                       accept=".pdf">
                                <small class="help-text">PDF format only (optional)</small>
                                {{ hasErrorForField($errors, 'file') }}
                            </div>
                        </fieldset>

                        <div class="form-group" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-success btn-block btn-gradient">
                                <i class="fa fa-plus-circle"></i> Create Invoice
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Invoice Guidelines --}}
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-lightbulb"></i> Invoice Guidelines</h3>
                </div>
                <div class="box-body">
                    <p style="font-size: 13px; margin-bottom: 10px;"><strong>Best Practices:</strong></p>
                    <ul style="margin-left: 20px; font-size: 12px;">
                        <li>Use unique invoice numbers (e.g., INV-2025-001)</li>
                        <li>Always upload PDF documentation</li>
                        <li>Match invoice date to actual receipt date</li>
                        <li>Verify amounts include VAT</li>
                    </ul>
                    <hr>
                    <p style="font-size: 13px; margin-bottom: 10px;"><strong>Required Information:</strong></p>
                    <ul style="margin-left: 20px; font-size: 12px;">
                        <li><strong>Invoice #:</strong> Supplier's invoice number</li>
                        <li><strong>Order #:</strong> Internal PO reference</li>
                        <li><strong>Division:</strong> Budget allocation department</li>
                        <li><strong>Supplier:</strong> Vendor providing goods/services</li>
                    </ul>
                </div>
            </div>

            {{-- Financial Statistics --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-chart-bar"></i> Financial Statistics</h3>
                </div>
                <div class="box-body">
                    @php
                        $totalInvoiced = $invoices->sum('total');
                        $currentMonthInvoices = $invoices->filter(function($inv) {
                            return \Carbon\Carbon::parse($inv->invoiced_date)->isCurrentMonth();
                        });
                        $currentMonthTotal = $currentMonthInvoices->sum('total');
                    @endphp

                    <div class="info-box bg-green" style="min-height: 80px; margin-bottom: 15px;">
                        <span class="info-box-icon"><i class="fa fa-file-invoice-dollar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Invoiced</span>
                            <span class="info-box-number">Rp {{ number_format($totalInvoiced, 2) }}</span>
                            <span class="progress-description">
                                All time
                            </span>
                        </div>
                    </div>

                    <div class="info-box bg-aqua" style="min-height: 80px; margin-bottom: 0;">
                        <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">This Month</span>
                            <span class="info-box-number">Rp {{ number_format($currentMonthTotal, 2) }}</span>
                            <span class="progress-description">
                                {{ $currentMonthInvoices->count() }} invoice(s)
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2
        $(".supplier_id, .division_id").select2({
            placeholder: function() {
                return $(this).data('placeholder') || "-- Select --";
            },
            allowClear: true
        });

        @if($invoices->count() > 0)
        // Initialize DataTable
        $('#table').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[5, "desc"]], // Sort by invoice date descending
            columnDefs: [
                { orderable: false, targets: 6 } // Actions column
            ],
            language: {
                search: "Search invoices:",
                lengthMenu: "Show _MENU_ invoices per page",
                info: "Showing _START_ to _END_ of _TOTAL_ invoices",
                infoEmpty: "No invoices available",
                infoFiltered: "(filtered from _MAX_ total invoices)",
                zeroRecords: "No matching invoices found"
            },
            dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>' +
                 '<"row"<"col-sm-12"<"table-responsive"tr>>>' +
                 '<"row"<"col-sm-5"i><"col-sm-7"p>>B',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5]
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="fa fa-file-csv"></i> CSV',
                    className: 'btn btn-info btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5]
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5]
                    },
                    customize: function(doc) {
                        doc.content[1].table.widths = ['15%', '15%', '20%', '15%', '20%', '15%'];
                    }
                },
                {
                    extend: 'copy',
                    text: '<i class="fa fa-copy"></i> Copy',
                    className: 'btn btn-default btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5]
                    }
                }
            ]
        });
        @endif

        // Form validation
        $('#createInvoiceForm').on('submit', function(e) {
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


