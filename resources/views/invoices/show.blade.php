@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Invoice Details',
    'subtitle' => $invoice->invoice_number,
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Invoices', 'url' => url('invoices')],
        ['label' => 'Details']
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

    <div class="row">
        {{-- Main Content --}}
        <div class="col-md-9">
            {{-- Invoice Metadata --}}
            <div class="info-box-wrapper">
                <div class="metadata-badge"><i class="fa fa-hashtag"></i> ID: {{ $invoice->id }}</div>
                <div class="metadata-badge"><i class="fa fa-calendar-plus"></i> Created: {{ $invoice->created_at->format('M d, Y') }}</div>
                <div class="metadata-badge"><i class="fa fa-clock"></i> Updated: {{ $invoice->updated_at->format('M d, Y H:i') }}</div>
                @if(Storage::exists('invoices/' . $invoice->invoice_number . '.pdf'))
                    <div class="metadata-badge"><i class="fa fa-file-pdf text-success"></i> PDF: Available</div>
                @else
                    <div class="metadata-badge"><i class="fa fa-file-pdf text-muted"></i> PDF: Not uploaded</div>
                @endif
            </div>

            {{-- Invoice Information --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-file-invoice"></i> Invoice Information</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <span class="detail-label"><i class="fa fa-hashtag"></i> Invoice Number:</span>
                                <span class="detail-value">{{ $invoice->invoice_number }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <span class="detail-label"><i class="fa fa-receipt"></i> Order/PO Number:</span>
                                <span class="detail-value">{{ $invoice->order_number }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <span class="detail-label"><i class="fa fa-calendar"></i> Invoice Date:</span>
                                <span class="detail-value">{{ \Carbon\Carbon::parse($invoice->invoiced_date)->format('F d, Y') }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <span class="detail-label"><i class="fa fa-money-bill-wave"></i> Total Amount (Incl. VAT):</span>
                                <span class="detail-value" style="color: #28a745; font-size: 24px; font-weight: bold;">
                                    Rp {{ number_format($invoice->total, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Supplier Information --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-truck"></i> Supplier Details</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <span class="detail-label"><i class="fa fa-building"></i> Supplier Name:</span>
                                <span class="detail-value">
                                    <a href="{{ url('suppliers/' . $invoice->supplier->id . '/edit') }}">
                                        {{ $invoice->supplier->name }}
                                    </a>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <span class="detail-label"><i class="fa fa-envelope"></i> Contact Email:</span>
                                <span class="detail-value">{{ $invoice->supplier->email ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <span class="detail-label"><i class="fa fa-phone"></i> Phone:</span>
                                <span class="detail-value">{{ $invoice->supplier->phone ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <span class="detail-label"><i class="fa fa-map-marker-alt"></i> Address:</span>
                                <span class="detail-value">{{ $invoice->supplier->address ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Division Information --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-sitemap"></i> Division Details</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <span class="detail-label"><i class="fa fa-sitemap"></i> Division Name:</span>
                                <span class="detail-value">
                                    <a href="{{ url('divisions/' . $invoice->division->id . '/edit') }}">
                                        {{ $invoice->division->name }}
                                    </a>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <span class="detail-label"><i class="fa fa-calendar-alt"></i> Fiscal Year:</span>
                                <span class="detail-value">
                                    {{ \Carbon\Carbon::parse($invoice->invoiced_date)->year }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @php
                        $divisionAssets = \App\Asset::where('division_id', $invoice->division_id)->get();
                        $divisionAssetValue = $divisionAssets->sum('purchase_cost');
                    @endphp

                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <span class="detail-label"><i class="fa fa-laptop"></i> Total Division Assets:</span>
                                <span class="detail-value">{{ $divisionAssets->count() }} assets</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <span class="detail-label"><i class="fa fa-coins"></i> Total Asset Value:</span>
                                <span class="detail-value">Rp {{ number_format($divisionAssetValue, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Related Assets --}}
            @php
                $relatedAssets = \App\Asset::where('invoice_id', $invoice->id)->get();
            @endphp
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-laptop"></i> Assets Purchased on This Invoice</h3>
                    <span class="badge badge-warning">{{ $relatedAssets->count() }}</span>
                </div>
                <div class="box-body">
                    @if($relatedAssets->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fa fa-barcode"></i> Asset Tag</th>
                                        <th><i class="fa fa-box"></i> Model</th>
                                        <th><i class="fa fa-info-circle"></i> Type</th>
                                        <th><i class="fa fa-money-bill-wave"></i> Cost</th>
                                        <th><i class="fa fa-calendar"></i> Purchase Date</th>
                                        <th><i class="fa fa-cog"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($relatedAssets as $asset)
                                        <tr>
                                            <td><strong>{{ $asset->asset_tag }}</strong></td>
                                            <td>{{ $asset->model->name ?? 'N/A' }}</td>
                                            <td>{{ $asset->type->name ?? 'N/A' }}</td>
                                            <td style="color: #28a745;">Rp {{ number_format($asset->purchase_cost, 2) }}</td>
                                            <td>{{ $asset->purchase_date ? \Carbon\Carbon::parse($asset->purchase_date)->format('M d, Y') : 'N/A' }}</td>
                                            <td>
                                                <a href="{{ url('assets/' . $asset->id . '/edit') }}" class="btn btn-sm btn-primary">
                                                    <i class="fa fa-edit"></i> Edit
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right">Total Assets Value:</th>
                                        <th style="color: #28a745; font-size: 16px;">
                                            R {{ number_format($relatedAssets->sum('purchase_cost'), 2) }}
                                        </th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fa fa-inbox"></i>
                            <p>No assets have been linked to this invoice yet.</p>
                            <p style="font-size: 12px; color: #777; margin-top: 10px;">
                                Assets will appear here when they are created with this invoice reference.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Budget Information --}}
            @php
                $fiscalYear = \Carbon\Carbon::parse($invoice->invoiced_date)->year;
                $divisionBudget = \App\Budget::where('division_id', $invoice->division_id)
                                             ->where('year', $fiscalYear)
                                             ->first();
                
                if ($divisionBudget) {
                    $budgetInvoices = \App\Invoice::where('division_id', $invoice->division_id)
                                                   ->whereYear('invoiced_date', $fiscalYear)
                                                   ->get();
                    $totalSpent = $budgetInvoices->sum('total');
                    $remaining = $divisionBudget->amount - $totalSpent;
                    $percentageUsed = $divisionBudget->amount > 0 ? ($totalSpent / $divisionBudget->amount) * 100 : 0;
                }
            @endphp

            @if(isset($divisionBudget))
                <div class="box box-{{ $percentageUsed >= 90 ? 'danger' : ($percentageUsed >= 75 ? 'warning' : 'success') }}">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-chart-line"></i> Budget Impact</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-box bg-aqua">
                                    <span class="info-box-icon"><i class="fa fa-wallet"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Budget {{ $fiscalYear }}</span>
                                        <span class="info-box-number">Rp {{ number_format($divisionBudget->amount, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="info-box bg-{{ $percentageUsed >= 90 ? 'red' : ($percentageUsed >= 75 ? 'yellow' : 'green') }}">
                                    <span class="info-box-icon"><i class="fa fa-money-bill-wave"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Spent</span>
                                        <span class="info-box-number">Rp {{ number_format($totalSpent, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="info-box bg-{{ $remaining >= 0 ? 'blue' : 'red' }}">
                                    <span class="info-box-icon"><i class="fa fa-coins"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Remaining</span>
                                        <span class="info-box-number">Rp {{ number_format($remaining, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="budget-progress-wrapper">
                            <div class="budget-progress-label">
                                <span>Budget Utilization</span>
                                <span><strong>{{ number_format($percentageUsed, 1) }}%</strong></span>
                            </div>
                            <div class="progress" style="height: 25px; margin-bottom: 10px;">
                                <div class="progress-bar progress-bar-{{ $percentageUsed >= 90 ? 'danger' : ($percentageUsed >= 75 ? 'warning' : 'success') }}" 
                                     role="progressbar" 
                                     aria-valuenow="{{ $percentageUsed }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100" 
                                     style="width: {{ min($percentageUsed, 100) }}%;">
                                    {{ number_format($percentageUsed, 1) }}%
                                </div>
                            </div>
                        </div>

                        @if($percentageUsed >= 100)
                            <div class="alert alert-danger" style="margin-top: 15px;">
                                <i class="fa fa-exclamation-triangle"></i> 
                                <strong>Budget Exceeded!</strong> This division has spent more than the allocated budget for {{ $fiscalYear }}.
                            </div>
                        @elseif($percentageUsed >= 90)
                            <div class="alert alert-warning" style="margin-top: 15px;">
                                <i class="fa fa-exclamation-circle"></i> 
                                <strong>High Usage Warning:</strong> This division has used over 90% of the allocated budget.
                            </div>
                        @else
                            <div class="alert alert-success" style="margin-top: 15px;">
                                <i class="fa fa-check-circle"></i> 
                                Budget is within normal limits for {{ $fiscalYear }}.
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-chart-line"></i> Budget Information</h3>
                    </div>
                    <div class="box-body">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> 
                            No budget has been set for <strong>{{ $invoice->division->name }}</strong> in fiscal year <strong>{{ $fiscalYear }}</strong>.
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-md-3">
            {{-- Quick Actions --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="box-body">
                    <a href="{{ url('invoices/' . $invoice->id . '/edit') }}" class="btn btn-primary btn-block">
                        <i class="fa fa-edit"></i> Edit Invoice
                    </a>
                    @if(Storage::exists('invoices/' . $invoice->invoice_number . '.pdf'))
                        <a href="{{ url('invoices/' . $invoice->id . '/pdf') }}" target="_blank" class="btn btn-success btn-block">
                            <i class="fa fa-file-pdf"></i> View PDF
                        </a>
                    @endif
                    <a href="{{ url('invoices') }}" class="btn btn-default btn-block">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
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

            {{-- Statistics --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-chart-bar"></i> Statistics</h3>
                </div>
                <div class="box-body">
                    <div class="info-box bg-green">
                        <span class="info-box-icon"><i class="fa fa-laptop"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Assets on Invoice</span>
                            <span class="info-box-number">{{ $relatedAssets->count() }}</span>
                        </div>
                    </div>

                    <div class="info-box bg-aqua">
                        <span class="info-box-icon"><i class="fa fa-coins"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Assets Total Value</span>
                            <span class="info-box-number" style="font-size: 18px;">Rp {{ number_format($relatedAssets->sum('purchase_cost'), 2) }}</span>
                        </div>
                    </div>

                    @if(isset($divisionBudget))
                        <div class="info-box bg-{{ $percentageUsed >= 90 ? 'red' : ($percentageUsed >= 75 ? 'yellow' : 'blue') }}">
                            <span class="info-box-icon"><i class="fa fa-percent"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Budget Used</span>
                                <span class="info-box-number">{{ number_format($percentageUsed, 1) }}%</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Related Links --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-link"></i> Related Links</h3>
                </div>
                <div class="box-body">
                    <ul style="list-style: none; padding: 0; margin: 0; font-size: 13px;">
                        <li style="margin-bottom: 8px;">
                            <i class="fa fa-chevron-right text-muted"></i> 
                            <a href="{{ url('suppliers/' . $invoice->supplier->id . '/edit') }}">View Supplier Details</a>
                        </li>
                        <li style="margin-bottom: 8px;">
                            <i class="fa fa-chevron-right text-muted"></i> 
                            <a href="{{ url('divisions/' . $invoice->division->id . '/edit') }}">View Division Details</a>
                        </li>
                        <li style="margin-bottom: 8px;">
                            <i class="fa fa-chevron-right text-muted"></i> 
                            <a href="{{ url('assets') }}?division={{ $invoice->division_id }}">View Division Assets</a>
                        </li>
                        @if(isset($divisionBudget))
                            <li style="margin-bottom: 8px;">
                                <i class="fa fa-chevron-right text-muted"></i> 
                                <a href="{{ url('budgets/' . $divisionBudget->id) }}">View {{ $fiscalYear }} Budget</a>
                            </li>
                        @endif
                        <li style="margin-bottom: 8px;">
                            <i class="fa fa-chevron-right text-muted"></i> 
                            <a href="{{ url('invoices') }}">View All Invoices</a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Information Box --}}
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Information</h3>
                </div>
                <div class="box-body">
                    <p style="font-size: 12px; margin-bottom: 10px;"><strong>About Invoices:</strong></p>
                    <p style="font-size: 12px; line-height: 1.6; color: #666;">
                        Invoices track all purchases made for the organization. Each invoice is linked to a supplier and division, 
                        and can have multiple assets associated with it. Always upload the PDF copy for record-keeping.
                    </p>
                    <hr>
                    <p style="font-size: 11px; color: #999; margin: 0;">
                        <i class="fa fa-calendar"></i> Created: {{ $invoice->created_at->format('M d, Y') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Auto-dismiss alerts
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });
</script>
@endpush
