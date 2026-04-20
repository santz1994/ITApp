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

@include('layouts.partials.module-toolbar', [
    'englishButtonId' => 'budgetShowLanguageEnglish',
    'indonesianButtonId' => 'budgetShowLanguageIndonesian',
    'ariaLabel' => 'Budget Show Language Toggle',
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
                            <strong><i class="fa fa-hashtag"></i> <span data-i18n="budgets.show.meta.id">Budget ID:</span></strong> 
                            #{{ $budget->id }}
                        </div>
                        <div class="col-md-3">
                            <strong><i class="fa fa-calendar"></i> <span data-i18n="budgets.show.meta.created">Created:</span></strong> 
                            {{ $budget->created_at ? $budget->created_at->format('M d, Y h:i A') : 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong><i class="fa fa-clock"></i> <span data-i18n="budgets.show.meta.updated">Last Updated:</span></strong> 
                            {{ $budget->updated_at ? $budget->updated_at->format('M d, Y h:i A') : 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong><i class="fa fa-sitemap"></i> <span data-i18n="budgets.show.meta.division">Division:</span></strong> 
                            {{ $budget->division->name }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Budget Details --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-money-bill-wave"></i> <span data-i18n="budgets.show.section.info">Budget Information</span></h3>
                </div>
                <div class="box-body">
                    <fieldset>
                        <legend><span class="form-section-icon"><i class="fa fa-info-circle"></i></span> <span data-i18n="budgets.show.section.financial_details">Financial Details</span></legend>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fa fa-sitemap"></i> <span data-i18n="budgets.show.label.division">Division:</span></label>
                                    <p class="form-control-static" style="font-size: 16px; font-weight: bold; color: #333;">
                                        {{ $budget->division->name }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fa fa-calendar"></i> <span data-i18n="budgets.show.label.year">Fiscal Year:</span></label>
                                    <p class="form-control-static">
                                        <span class="badge bg-blue" style="font-size: 16px; padding: 8px 15px;">
                                            {{ $budget->year }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fa fa-money-bill-wave"></i> <span data-i18n="budgets.show.label.total_budget">Total Budget:</span></label>
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
                    <h3 class="box-title"><i class="fa fa-chart-line"></i> <span data-i18n="budgets.show.section.utilization">Budget Utilization</span></h3>
                </div>
                <div class="box-body">
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-md-4">
                            <div class="info-box bg-aqua">
                                <span class="info-box-icon"><i class="fa fa-money-bill-wave"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text" data-i18n="budgets.show.utilization.total_budget">Total Budget</span>
                                    <span class="info-box-number">Rp {{ number_format($budget->total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-{{ $percentageUsed > 90 ? 'red' : ($percentageUsed > 75 ? 'yellow' : 'green') }}">
                                <span class="info-box-icon"><i class="fa fa-shopping-cart"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text" data-i18n="budgets.show.utilization.total_spent">Total Spent</span>
                                    <span class="info-box-number">Rp {{ number_format($totalSpent, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-{{ $remaining < 0 ? 'red' : 'blue' }}">
                                <span class="info-box-icon"><i class="fa fa-piggy-bank"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text" data-i18n="budgets.show.utilization.remaining">Remaining</span>
                                    <span class="info-box-number">Rp {{ number_format($remaining, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label><span data-i18n="budgets.show.utilization.usage">Budget Usage:</span> {{ number_format($percentageUsed, 1) }}%</label>
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
                                    <strong data-i18n="budgets.show.utilization.over_budget">Over Budget:</strong> <span data-i18n="budgets.show.utilization.over_budget_desc">Spending exceeds allocated budget by</span> Rp {{ number_format(abs($remaining), 2) }}
                                </p>
                            @elseif($percentageUsed > 90)
                                <p class="text-warning" style="margin-top: 10px;">
                                    <i class="fa fa-exclamation-circle"></i> 
                                    <strong data-i18n="budgets.show.utilization.warning">Warning:</strong> <span data-i18n="budgets.show.utilization.warning_desc">Budget utilization is at</span> {{ number_format($percentageUsed, 1) }}%
                                </p>
                            @else
                                <p class="text-success" style="margin-top: 10px;">
                                    <i class="fa fa-check-circle"></i> 
                                    <span data-i18n="budgets.show.utilization.healthy_desc">Budget utilization is within acceptable limits</span>
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
                        <i class="fa fa-file-invoice-dollar"></i> <span data-i18n="budgets.show.section.invoices">Related Invoices</span> ({{ $budget->year }})
                        <span class="count-badge">{{ $invoices->count() }}</span>
                    </h3>
                </div>
                <div class="box-body">
                    @if($invoices->count() > 0)
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th data-i18n="budgets.show.table.invoice">Invoice #</th>
                                    <th data-i18n="budgets.show.table.supplier">Supplier</th>
                                    <th data-i18n="budgets.show.table.date">Date</th>
                                    <th data-i18n="budgets.show.table.amount">Amount</th>
                                    <th style="width: 100px;" data-i18n="budgets.show.table.actions">Actions</th>
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
                                            <a href="{{ url('invoices/' . $invoice->id . '/edit') }}" class="btn btn-sm btn-primary" title="Edit Invoice" data-i18n-title="budgets.show.action.edit_invoice_title">
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
                                                <i class="fa fa-eye"></i> <span data-i18n="budgets.show.action.view_all_invoices">View All</span> {{ $invoices->count() }} <span data-i18n="budgets.show.section.invoices">Invoices</span>
                                            </a>
                                        </td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    @else
                        <div class="text-center empty-state" style="padding: 30px;">
                            <i class="fa fa-file-invoice-dollar fa-3x" style="opacity: 0.3; margin-bottom: 15px;"></i>
                            <p><span data-i18n="budgets.show.empty.invoices">No invoices recorded for</span> {{ $budget->year }}.</p>
                            <p class="text-muted" style="font-size: 12px;" data-i18n="budgets.show.empty.invoices_desc">Invoices will appear here when they are assigned to this division and year.</p>
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
                        <i class="fa fa-laptop"></i> <span data-i18n="budgets.show.section.assets">Division Assets</span>
                        <span class="count-badge">{{ $divisionAssets->count() }}</span>
                    </h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong><i class="fa fa-laptop"></i> <span data-i18n="budgets.show.assets.total_assets">Total Assets:</span></strong> {{ $divisionAssets->count() }}</p>
                            <p><strong><i class="fa fa-money-bill-wave"></i> <span data-i18n="budgets.show.assets.total_value">Total Asset Value:</span></strong> 
                                <span style="color: #28a745; font-weight: bold;">Rp {{ number_format($totalAssetValue, 2) }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong><i class="fa fa-users"></i> <span data-i18n="budgets.show.assets.division_users">Division Users:</span></strong> 
                                {{ \App\User::where('division_id', $budget->division_id)->count() }}
                            </p>
                            <a href="{{ url('assets?division=' . $budget->division_id) }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-eye"></i> <span data-i18n="budgets.show.action.view_division_assets">View Division Assets</span>
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
                    <h3 class="box-title"><i class="fa fa-bolt"></i> <span data-i18n="budgets.show.quick_actions.title">Quick Actions</span></h3>
                </div>
                <div class="box-body">
                    <a href="{{ url('budgets/' . $budget->id . '/edit') }}" class="btn btn-primary btn-block">
                        <i class="fa fa-edit"></i> <span data-i18n="budgets.show.action.edit">Edit Budget</span>
                    </a>
                    <a href="{{ url('budgets') }}" class="btn btn-default btn-block">
                        <i class="fa fa-arrow-left"></i> <span data-i18n="budgets.show.action.back">Back to List</span>
                    </a>
                    <hr>
                    <form method="POST" action="{{ url('budgets/' . $budget->id) }}" id="budgetShowDeleteForm">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fa fa-trash"></i> <span data-i18n="budgets.show.action.delete">Delete Budget</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Statistics --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-chart-bar"></i> <span data-i18n="budgets.show.statistics.title">Statistics</span></h3>
                </div>
                <div class="box-body">
                    <div class="info-box bg-green" style="min-height: 80px; margin-bottom: 15px;">
                        <span class="info-box-icon"><i class="fa fa-file-invoice-dollar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text" data-i18n="budgets.show.statistics.invoices">Invoices</span>
                            <span class="info-box-number">{{ $invoices->count() }}</span>
                            <span class="progress-description">
                                <span data-i18n="budgets.show.statistics.for_year">For</span> {{ $budget->year }}
                            </span>
                        </div>
                    </div>

                    <div class="info-box bg-{{ $percentageUsed > 90 ? 'red' : ($percentageUsed > 75 ? 'yellow' : 'aqua') }}" style="min-height: 80px; margin-bottom: 0;">
                        <span class="info-box-icon"><i class="fa fa-percent"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text" data-i18n="budgets.show.statistics.budget_used">Budget Used</span>
                            <span class="info-box-number">{{ number_format($percentageUsed, 1) }}%</span>
                            <span class="progress-description">
                                Rp {{ number_format($remaining, 2) }} <span data-i18n="budgets.show.statistics.remaining">remaining</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Related Links --}}
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-link"></i> <span data-i18n="budgets.show.related_links.title">Related Links</span></h3>
                </div>
                <div class="box-body">
                    <ul style="list-style: none; padding-left: 0;">
                        <li style="margin-bottom: 10px;">
                            <a href="{{ url('divisions/' . $budget->division_id) }}">
                                <i class="fa fa-sitemap text-primary"></i> <span data-i18n="budgets.show.related_links.division">View Division Details</span>
                            </a>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <a href="{{ url('invoices?division=' . $budget->division_id . '&year=' . $budget->year) }}">
                                <i class="fa fa-file-invoice-dollar text-success"></i> <span data-i18n="budgets.show.related_links.invoices">View All Invoices</span> ({{ $budget->year }})
                            </a>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <a href="{{ url('assets?division=' . $budget->division_id) }}">
                                <i class="fa fa-laptop text-info"></i> <span data-i18n="budgets.show.related_links.assets">View Division Assets</span>
                            </a>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <a href="{{ url('budgets') }}">
                                <i class="fa fa-money-bill-wave text-warning"></i> <span data-i18n="budgets.show.related_links.budgets">View All Budgets</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Information Box --}}
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> <span data-i18n="budgets.show.information.title">Information</span></h3>
                </div>
                <div class="box-body">
                    <p style="font-size: 13px;">
                        <i class="fa fa-lightbulb text-warning"></i> <strong data-i18n="budgets.show.information.about">About Budgets:</strong>
                    </p>
                    <p style="font-size: 12px; color: #666;" data-i18n="budgets.show.information.about_desc">
                        Budgets help track financial allocations per division for each fiscal year. Monitor spending against budgets to ensure financial compliance.
                    </p>
                    <hr>
                    <p style="font-size: 12px; color: #666;">
                        <i class="fa fa-chart-line text-info"></i> 
                        <strong data-i18n="budgets.show.information.status">Budget Status:</strong> 
                        @if($percentageUsed > 100)
                            <span class="text-danger" data-i18n="budgets.show.information.status_over">Over Budget</span>
                        @elseif($percentageUsed > 90)
                            <span class="text-warning" data-i18n="budgets.show.information.status_critical">Critical</span>
                        @elseif($percentageUsed > 75)
                            <span class="text-warning" data-i18n="budgets.show.information.status_high">High Usage</span>
                        @else
                            <span class="text-success" data-i18n="budgets.show.information.status_healthy">Healthy</span>
                        @endif
                    </p>
                    <hr>
                    <p style="font-size: 12px; color: #666; margin-bottom: 0;">
                        <i class="fa fa-exclamation-triangle text-danger"></i> 
                        <strong data-i18n="budgets.show.information.notes">Notes:</strong> <span data-i18n="budgets.show.information.notes_desc">Deleting this budget will not affect related invoices.</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    (function() {
        var translations = {
            en: {
                'budgets.show.meta.id': 'Budget ID:',
                'budgets.show.meta.created': 'Created:',
                'budgets.show.meta.updated': 'Last Updated:',
                'budgets.show.meta.division': 'Division:',
                'budgets.show.section.info': 'Budget Information',
                'budgets.show.section.financial_details': 'Financial Details',
                'budgets.show.label.division': 'Division:',
                'budgets.show.label.year': 'Fiscal Year:',
                'budgets.show.label.total_budget': 'Total Budget:',
                'budgets.show.section.utilization': 'Budget Utilization',
                'budgets.show.utilization.total_budget': 'Total Budget',
                'budgets.show.utilization.total_spent': 'Total Spent',
                'budgets.show.utilization.remaining': 'Remaining',
                'budgets.show.utilization.usage': 'Budget Usage:',
                'budgets.show.utilization.over_budget': 'Over Budget:',
                'budgets.show.utilization.over_budget_desc': 'Spending exceeds allocated budget by',
                'budgets.show.utilization.warning': 'Warning:',
                'budgets.show.utilization.warning_desc': 'Budget utilization is at',
                'budgets.show.utilization.healthy_desc': 'Budget utilization is within acceptable limits',
                'budgets.show.section.invoices': 'Related Invoices',
                'budgets.show.table.invoice': 'Invoice #',
                'budgets.show.table.supplier': 'Supplier',
                'budgets.show.table.date': 'Date',
                'budgets.show.table.amount': 'Amount',
                'budgets.show.table.actions': 'Actions',
                'budgets.show.action.edit_invoice_title': 'Edit Invoice',
                'budgets.show.action.view_all_invoices': 'View All',
                'budgets.show.empty.invoices': 'No invoices recorded for',
                'budgets.show.empty.invoices_desc': 'Invoices will appear here when they are assigned to this division and year.',
                'budgets.show.section.assets': 'Division Assets',
                'budgets.show.assets.total_assets': 'Total Assets:',
                'budgets.show.assets.total_value': 'Total Asset Value:',
                'budgets.show.assets.division_users': 'Division Users:',
                'budgets.show.action.view_division_assets': 'View Division Assets',
                'budgets.show.quick_actions.title': 'Quick Actions',
                'budgets.show.action.edit': 'Edit Budget',
                'budgets.show.action.back': 'Back to List',
                'budgets.show.action.delete': 'Delete Budget',
                'budgets.show.statistics.title': 'Statistics',
                'budgets.show.statistics.invoices': 'Invoices',
                'budgets.show.statistics.for_year': 'For',
                'budgets.show.statistics.budget_used': 'Budget Used',
                'budgets.show.statistics.remaining': 'remaining',
                'budgets.show.related_links.title': 'Related Links',
                'budgets.show.related_links.division': 'View Division Details',
                'budgets.show.related_links.invoices': 'View All Invoices',
                'budgets.show.related_links.assets': 'View Division Assets',
                'budgets.show.related_links.budgets': 'View All Budgets',
                'budgets.show.information.title': 'Information',
                'budgets.show.information.about': 'About Budgets:',
                'budgets.show.information.about_desc': 'Budgets help track financial allocations per division for each fiscal year. Monitor spending against budgets to ensure financial compliance.',
                'budgets.show.information.status': 'Budget Status:',
                'budgets.show.information.status_over': 'Over Budget',
                'budgets.show.information.status_critical': 'Critical',
                'budgets.show.information.status_high': 'High Usage',
                'budgets.show.information.status_healthy': 'Healthy',
                'budgets.show.information.notes': 'Notes:',
                'budgets.show.information.notes_desc': 'Deleting this budget will not affect related invoices.',
                'budgets.show.runtime.delete_confirm': 'Are you sure you want to delete this budget? This action cannot be undone.'
            },
            id: {
                'budgets.show.meta.id': 'ID Anggaran:',
                'budgets.show.meta.created': 'Dibuat:',
                'budgets.show.meta.updated': 'Terakhir Diperbarui:',
                'budgets.show.meta.division': 'Divisi:',
                'budgets.show.section.info': 'Informasi Anggaran',
                'budgets.show.section.financial_details': 'Detail Keuangan',
                'budgets.show.label.division': 'Divisi:',
                'budgets.show.label.year': 'Tahun Fiskal:',
                'budgets.show.label.total_budget': 'Total Anggaran:',
                'budgets.show.section.utilization': 'Utilisasi Anggaran',
                'budgets.show.utilization.total_budget': 'Total Anggaran',
                'budgets.show.utilization.total_spent': 'Total Terpakai',
                'budgets.show.utilization.remaining': 'Sisa',
                'budgets.show.utilization.usage': 'Pemakaian Anggaran:',
                'budgets.show.utilization.over_budget': 'Melebihi Anggaran:',
                'budgets.show.utilization.over_budget_desc': 'Pengeluaran melebihi alokasi anggaran sebesar',
                'budgets.show.utilization.warning': 'Peringatan:',
                'budgets.show.utilization.warning_desc': 'Utilisasi anggaran berada di',
                'budgets.show.utilization.healthy_desc': 'Utilisasi anggaran masih dalam batas wajar',
                'budgets.show.section.invoices': 'Faktur Terkait',
                'budgets.show.table.invoice': 'No. Faktur',
                'budgets.show.table.supplier': 'Pemasok',
                'budgets.show.table.date': 'Tanggal',
                'budgets.show.table.amount': 'Jumlah',
                'budgets.show.table.actions': 'Aksi',
                'budgets.show.action.edit_invoice_title': 'Ubah Faktur',
                'budgets.show.action.view_all_invoices': 'Lihat Semua',
                'budgets.show.empty.invoices': 'Belum ada faktur untuk',
                'budgets.show.empty.invoices_desc': 'Faktur akan tampil di sini saat sudah ditetapkan ke divisi dan tahun ini.',
                'budgets.show.section.assets': 'Aset Divisi',
                'budgets.show.assets.total_assets': 'Total Aset:',
                'budgets.show.assets.total_value': 'Total Nilai Aset:',
                'budgets.show.assets.division_users': 'Pengguna Divisi:',
                'budgets.show.action.view_division_assets': 'Lihat Aset Divisi',
                'budgets.show.quick_actions.title': 'Aksi Cepat',
                'budgets.show.action.edit': 'Ubah Anggaran',
                'budgets.show.action.back': 'Kembali ke Daftar',
                'budgets.show.action.delete': 'Hapus Anggaran',
                'budgets.show.statistics.title': 'Statistik',
                'budgets.show.statistics.invoices': 'Faktur',
                'budgets.show.statistics.for_year': 'Untuk',
                'budgets.show.statistics.budget_used': 'Anggaran Terpakai',
                'budgets.show.statistics.remaining': 'tersisa',
                'budgets.show.related_links.title': 'Tautan Terkait',
                'budgets.show.related_links.division': 'Lihat Detail Divisi',
                'budgets.show.related_links.invoices': 'Lihat Semua Faktur',
                'budgets.show.related_links.assets': 'Lihat Aset Divisi',
                'budgets.show.related_links.budgets': 'Lihat Semua Anggaran',
                'budgets.show.information.title': 'Informasi',
                'budgets.show.information.about': 'Tentang Anggaran:',
                'budgets.show.information.about_desc': 'Anggaran membantu melacak alokasi keuangan per divisi untuk setiap tahun fiskal. Pantau pengeluaran terhadap anggaran untuk memastikan kepatuhan finansial.',
                'budgets.show.information.status': 'Status Anggaran:',
                'budgets.show.information.status_over': 'Melebihi Anggaran',
                'budgets.show.information.status_critical': 'Kritis',
                'budgets.show.information.status_high': 'Pemakaian Tinggi',
                'budgets.show.information.status_healthy': 'Sehat',
                'budgets.show.information.notes': 'Catatan:',
                'budgets.show.information.notes_desc': 'Menghapus anggaran ini tidak akan memengaruhi faktur terkait.',
                'budgets.show.runtime.delete_confirm': 'Apakah Anda yakin ingin menghapus anggaran ini? Tindakan ini tidak dapat dibatalkan.'
            }
        };

        var currentLanguage = 'en';
        var userId = '{{ (int) auth()->id() }}';
        var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
        var englishButton = document.getElementById('budgetShowLanguageEnglish');
        var indonesianButton = document.getElementById('budgetShowLanguageIndonesian');

        function getLanguage() {
            try {
                var raw = window.localStorage.getItem(languageStorageKey);
                if (!raw) {
                    return 'en';
                }

                var parsed = JSON.parse(raw);
                return parsed && parsed.language === 'id' ? 'id' : 'en';
            } catch (error) {
                return 'en';
            }
        }

        function saveLanguage(language) {
            try {
                var raw = window.localStorage.getItem(languageStorageKey);
                var parsed = raw ? JSON.parse(raw) : {};
                parsed.language = language === 'id' ? 'id' : 'en';
                window.localStorage.setItem(languageStorageKey, JSON.stringify(parsed));
            } catch (error) {
                // Keep silent if localStorage is unavailable.
            }
        }

        function getLabel(key, fallback) {
            var dictionary = translations[currentLanguage] || translations.en;
            return dictionary[key] || fallback || key;
        }

        function applyLanguage(language) {
            currentLanguage = language === 'id' ? 'id' : 'en';
            var dictionary = translations[currentLanguage] || translations.en;

            Array.prototype.forEach.call(document.querySelectorAll('[data-i18n]'), function(node) {
                var key = node.getAttribute('data-i18n');
                if (dictionary[key]) {
                    node.textContent = dictionary[key];
                }
            });

            Array.prototype.forEach.call(document.querySelectorAll('[data-i18n-title]'), function(node) {
                var key = node.getAttribute('data-i18n-title');
                if (dictionary[key]) {
                    node.setAttribute('title', dictionary[key]);
                }
            });

            if (englishButton && indonesianButton) {
                englishButton.classList.toggle('active', currentLanguage === 'en');
                indonesianButton.classList.toggle('active', currentLanguage === 'id');
            }
        }

        window.budgetShowLabel = getLabel;

        if (englishButton && indonesianButton) {
            englishButton.addEventListener('click', function() {
                saveLanguage('en');
                applyLanguage('en');
            });

            indonesianButton.addEventListener('click', function() {
                saveLanguage('id');
                applyLanguage('id');
            });
        }

        $(document).ready(function() {
            $('#budgetShowDeleteForm').on('submit', function(e) {
                if (!confirm(window.budgetShowLabel('budgets.show.runtime.delete_confirm', 'Are you sure you want to delete this budget? This action cannot be undone.'))) {
                    e.preventDefault();
                    return false;
                }
            });

            // Auto-dismiss alerts
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            applyLanguage(getLanguage());
        });
    })();
</script>
@endpush
