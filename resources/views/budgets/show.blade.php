@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Budget Details',
    'subtitle' => $budget->division->name . ' - Fiscal Year ' . $budget->year,
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Budgets', 'url' => url('budgets')],
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="row">
        {{-- Main Content --}}
        <div class="col-md-9">
            {{-- Budget Metadata --}}
            <div class="box box-warning">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong><i class="fa fa-hashtag"></i> Budget ID:</strong> 
                            #{{ $budget->id }}
                        </div>
                        <div class="col-md-3">
                            <strong><i class="fa fa-calendar"></i> Created:</strong> 
                            {{ $budget->created_at ? $budget->created_at->format('M d, Y h:i A') : 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong><i class="fa fa-clock"></i> Last Updated:</strong> 
                            {{ $budget->updated_at ? $budget->updated_at->format('M d, Y h:i A') : 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong><i class="fa fa-sitemap"></i> Division:</strong> 
                            {{ $budget->division->name }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Budget Details --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-money-bill-wave"></i> Budget Information</h3>
                </div>
                <div class="box-body">
                    <fieldset>
                        <legend><span class="form-section-icon"><i class="fa fa-info-circle"></i></span> Financial Details</legend>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fa fa-sitemap"></i> Division:</label>
                                    <p class="form-control-static" style="font-size: 16px; font-weight: bold; color: #333;">
                                        {{ $budget->division->name }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fa fa-calendar"></i> Fiscal Year:</label>
                                    <p class="form-control-static">
                                        <span class="badge bg-blue" style="font-size: 16px; padding: 8px 15px;">
                                            {{ $budget->year }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fa fa-money-bill-wave"></i> Total Budget:</label>
                                    <p class="form-control-static" style="font-size: 18px; font-weight: bold; color: #28a745;">
                                        Rp {{ number_format($budget->total, 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>

            {{-- Related Invoices --}}
            @php
                $invoices = \App\Invoice::where('division_id', $budget->division_id)
                                        ->whereYear('invoiced_date', $budget->year)
                                        ->get();
                $totalSpent = $invoices->sum('total');
                $remaining = $budget->total - $totalSpent;
                $percentageUsed = $budget->total > 0 ? ($totalSpent / $budget->total) * 100 : 0;
            @endphp

            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-chart-line"></i> Budget Utilization</h3>
                </div>
                <div class="box-body">
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-md-4">
                            <div class="info-box bg-aqua">
                                <span class="info-box-icon"><i class="fa fa-money-bill-wave"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Budget</span>
                                    <span class="info-box-number">Rp {{ number_format($budget->total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-{{ $percentageUsed > 90 ? 'red' : ($percentageUsed > 75 ? 'yellow' : 'green') }}">
                                <span class="info-box-icon"><i class="fa fa-shopping-cart"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Spent</span>
                                    <span class="info-box-number">Rp {{ number_format($totalSpent, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-{{ $remaining < 0 ? 'red' : 'blue' }}">
                                <span class="info-box-icon"><i class="fa fa-piggy-bank"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Remaining</span>
                                    <span class="info-box-number">Rp {{ number_format($remaining, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label>Budget Usage: {{ number_format($percentageUsed, 1) }}%</label>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar progress-bar-{{ $percentageUsed > 90 ? 'danger' : ($percentageUsed > 75 ? 'warning' : 'success') }}" 
                                     role="progressbar" 
                                     aria-valuenow="{{ $percentageUsed }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100" 
                                     style="width: {{ min($percentageUsed, 100) }}%; font-size: 14px; line-height: 25px;">
                                    {{ number_format($percentageUsed, 1) }}%
                                </div>
                            </div>
                            @if($percentageUsed > 100)
                                <p class="text-danger" style="margin-top: 10px;">
                                    <i class="fa fa-exclamation-triangle"></i> 
                                    <strong>Over Budget:</strong> Spending exceeds allocated budget by Rp {{ number_format(abs($remaining), 2) }}
                                </p>
                            @elseif($percentageUsed > 90)
                                <p class="text-warning" style="margin-top: 10px;">
                                    <i class="fa fa-exclamation-circle"></i> 
                                    <strong>Warning:</strong> Budget utilization is at {{ number_format($percentageUsed, 1) }}%
                                </p>
                            @else
                                <p class="text-success" style="margin-top: 10px;">
                                    <i class="fa fa-check-circle"></i> 
                                    Budget utilization is within acceptable limits
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Related Invoices Table --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-file-invoice-dollar"></i> Related Invoices ({{ $budget->year }})
                        <span class="count-badge">{{ $invoices->count() }}</span>
                    </h3>
                </div>
                <div class="box-body">
                    @if($invoices->count() > 0)
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Supplier</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices->take(10) as $invoice)
                                    <tr>
                                        <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                        <td>{{ $invoice->supplier->name ?? 'N/A' }}</td>
                                        <td>{{ $invoice->invoiced_date ? \Carbon\Carbon::parse($invoice->invoiced_date)->format('M d, Y') : 'N/A' }}</td>
                                        <td><strong style="color: #28a745;">Rp {{ number_format($invoice->total, 2) }}</strong></td>
                                        <td>
                                            <a href="{{ url('invoices/' . $invoice->id . '/edit') }}" class="btn btn-sm btn-primary" title="Edit Invoice">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            @if($invoices->count() > 10)
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <a href="{{ url('invoices?division=' . $budget->division_id . '&year=' . $budget->year) }}" class="btn btn-sm btn-info">
                                                <i class="fa fa-eye"></i> View All {{ $invoices->count() }} Invoices
                                            </a>
                                        </td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    @else
                        <div class="text-center empty-state" style="padding: 30px;">
                            <i class="fa fa-file-invoice-dollar fa-3x" style="opacity: 0.3; margin-bottom: 15px;"></i>
                            <p>No invoices recorded for {{ $budget->year }}.</p>
                            <p class="text-muted" style="font-size: 12px;">Invoices will appear here when they are assigned to this division and year.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Division Assets Summary --}}
            @php
                $divisionAssets = \App\Asset::where('division_id', $budget->division_id)->get();
                $totalAssetValue = $divisionAssets->sum('purchase_cost');
            @endphp

            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-laptop"></i> Division Assets
                        <span class="count-badge">{{ $divisionAssets->count() }}</span>
                    </h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong><i class="fa fa-laptop"></i> Total Assets:</strong> {{ $divisionAssets->count() }}</p>
                            <p><strong><i class="fa fa-money-bill-wave"></i> Total Asset Value:</strong> 
                                <span style="color: #28a745; font-weight: bold;">Rp {{ number_format($totalAssetValue, 2) }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong><i class="fa fa-users"></i> Division Users:</strong> 
                                {{ \App\User::where('division_id', $budget->division_id)->count() }}
                            </p>
                            <a href="{{ url('assets?division=' . $budget->division_id) }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-eye"></i> View Division Assets
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-md-3">
            {{-- Quick Actions --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="box-body">
                    <a href="{{ url('budgets/' . $budget->id . '/edit') }}" class="btn btn-primary btn-block">
                        <i class="fa fa-edit"></i> Edit Budget
                    </a>
                    <a href="{{ url('budgets') }}" class="btn btn-default btn-block">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                    <hr>
                    <form method="POST" action="{{ url('budgets/' . $budget->id) }}" onsubmit="return confirm('Are you sure you want to delete this budget? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fa fa-trash"></i> Delete Budget
                        </button>
                    </form>
                </div>
            </div>

            {{-- Statistics --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-chart-bar"></i> Statistics</h3>
                </div>
                <div class="box-body">
                    <div class="info-box bg-green" style="min-height: 80px; margin-bottom: 15px;">
                        <span class="info-box-icon"><i class="fa fa-file-invoice-dollar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Invoices</span>
                            <span class="info-box-number">{{ $invoices->count() }}</span>
                            <span class="progress-description">
                                For {{ $budget->year }}
                            </span>
                        </div>
                    </div>

                    <div class="info-box bg-{{ $percentageUsed > 90 ? 'red' : ($percentageUsed > 75 ? 'yellow' : 'aqua') }}" style="min-height: 80px; margin-bottom: 0;">
                        <span class="info-box-icon"><i class="fa fa-percent"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Budget Used</span>
                            <span class="info-box-number">{{ number_format($percentageUsed, 1) }}%</span>
                            <span class="progress-description">
                                Rp {{ number_format($remaining, 2) }} remaining
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Related Links --}}
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-link"></i> Related Links</h3>
                </div>
                <div class="box-body">
                    <ul style="list-style: none; padding-left: 0;">
                        <li style="margin-bottom: 10px;">
                            <a href="{{ url('divisions/' . $budget->division_id) }}">
                                <i class="fa fa-sitemap text-primary"></i> View Division Details
                            </a>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <a href="{{ url('invoices?division=' . $budget->division_id . '&year=' . $budget->year) }}">
                                <i class="fa fa-file-invoice-dollar text-success"></i> View All Invoices ({{ $budget->year }})
                            </a>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <a href="{{ url('assets?division=' . $budget->division_id) }}">
                                <i class="fa fa-laptop text-info"></i> View Division Assets
                            </a>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <a href="{{ url('budgets') }}">
                                <i class="fa fa-money-bill-wave text-warning"></i> View All Budgets
                            </a>
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
                    <p style="font-size: 13px;">
                        <i class="fa fa-lightbulb text-warning"></i> <strong>About Budgets:</strong>
                    </p>
                    <p style="font-size: 12px; color: #666;">
                        Budgets help track financial allocations per division for each fiscal year. Monitor spending against budgets to ensure financial compliance.
                    </p>
                    <hr>
                    <p style="font-size: 12px; color: #666;">
                        <i class="fa fa-chart-line text-info"></i> 
                        <strong>Budget Status:</strong> 
                        @if($percentageUsed > 100)
                            <span class="text-danger">Over Budget</span>
                        @elseif($percentageUsed > 90)
                            <span class="text-warning">Critical</span>
                        @elseif($percentageUsed > 75)
                            <span class="text-warning">High Usage</span>
                        @else
                            <span class="text-success">Healthy</span>
                        @endif
                    </p>
                    <hr>
                    <p style="font-size: 12px; color: #666; margin-bottom: 0;">
                        <i class="fa fa-exclamation-triangle text-danger"></i> 
                        <strong>Notes:</strong> Deleting this budget will not affect related invoices.
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
