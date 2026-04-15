@extends('layouts.app')

@section('main-content')

@include('components.page-header', [
    'title' => 'Supplier Details',
    'subtitle' => $supplier->name,
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('admin.dashboard'), 'icon' => 'home'],
        ['label' => 'Suppliers', 'url' => route('suppliers.index')],
        ['label' => 'Details']
    ],
    'actions' => '
        <div class="btn-group" role="group">
            <a href="'.route('suppliers.edit', $supplier->id).'" class="btn btn-warning">
                <i class="fa fa-edit"></i> <span class="hidden-xs">Edit</span>
            </a>
            <a href="'.route('suppliers.index').'" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> <span class="hidden-xs">Back</span>
            </a>
        </div>
    '
])

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            {{-- Supplier Information --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-building"></i> Supplier Information</h3>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        <dt>Supplier Name:</dt>
                        <dd><strong>{{ $supplier->name }}</strong></dd>

                        <dt>Contact Person:</dt>
                        <dd>{{ $supplier->contact_person ?? '-' }}</dd>

                        <dt>Email:</dt>
                        <dd>{{ $supplier->email ?? '-' }}</dd>

                        <dt>Phone:</dt>
                        <dd>{{ $supplier->phone ?? '-' }}</dd>

                        <dt>Address:</dt>
                        <dd>{{ $supplier->address ?? '-' }}</dd>

                        <dt>Website:</dt>
                        <dd>
                            @if($supplier->website)
                                <a href="{{ $supplier->website }}" target="_blank">{{ $supplier->website }}</a>
                            @else
                                -
                            @endif
                        </dd>

                        <dt>Created:</dt>
                        <dd>{{ $supplier->created_at ? $supplier->created_at->format('d M Y H:i') : '-' }}</dd>

                        <dt>Last Updated:</dt>
                        <dd>{{ $supplier->updated_at ? $supplier->updated_at->format('d M Y H:i') : '-' }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Statistics --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bar-chart"></i> Statistics</h3>
                </div>
                <div class="box-body">
                    <div class="info-box bg-aqua">
                        <span class="info-box-icon"><i class="fa fa-laptop"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Assets</span>
                            <span class="info-box-number">{{ $assets->count() }}</span>
                        </div>
                    </div>

                    <div class="info-box bg-yellow">
                        <span class="info-box-icon"><i class="fa fa-file-text"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Invoices</span>
                            <span class="info-box-number">{{ $invoices->count() }}</span>
                        </div>
                    </div>

                    <div class="info-box bg-green">
                        <span class="info-box-icon"><i class="fa fa-dollar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Value</span>
                            <span class="info-box-number">R{{ number_format($assets->sum('purchase_cost'), 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            {{-- Assets from This Supplier --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-list"></i> Assets from This Supplier</h3>
                    <div class="box-tools">
                        <span class="label label-primary">{{ $assets->count() }} Assets</span>
                    </div>
                </div>
                <div class="box-body">
                    @if($assets->count() > 0)
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Asset Tag</th>
                                    <th>Model</th>
                                    <th>Serial Number</th>
                                    <th>Status</th>
                                    <th>Purchase Cost</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assets as $asset)
                                <tr>
                                    <td><strong>{{ $asset->asset_tag }}</strong></td>
                                    <td>{{ optional($asset->assetModel)->asset_model ?? '-' }}</td>
                                    <td>{{ $asset->serial_number ?? '-' }}</td>
                                    <td>
                                        <span class="label label-{{ $asset->status == 'deployed' ? 'success' : ($asset->status == 'ready' ? 'info' : ($asset->status == 'repairs' ? 'warning' : 'default')) }}">
                                            {{ ucfirst($asset->status) }}
                                        </span>
                                    </td>
                                    <td>R{{ number_format($asset->purchase_cost ?? 0, 2) }}</td>
                                    <td>
                                        <a href="{{ route('assets.show', $asset->id) }}" class="btn btn-xs btn-info">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> No assets have been purchased from this supplier yet.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Invoices from This Supplier --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-file-text"></i> Invoices from This Supplier</h3>
                    <div class="box-tools">
                        <span class="label label-success">{{ $invoices->count() }} Invoices</span>
                    </div>
                </div>
                <div class="box-body">
                    @if($invoices->count() > 0)
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Invoice Number</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $invoice)
                                <tr>
                                    <td><strong>{{ $invoice->invoice_number ?? '-' }}</strong></td>
                                    <td>{{ $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : '-' }}</td>
                                    <td>R{{ number_format($invoice->amount ?? 0, 2) }}</td>
                                    <td>
                                        <span class="label label-{{ $invoice->status == 'paid' ? 'success' : 'warning' }}">
                                            {{ ucfirst($invoice->status ?? 'pending') }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-xs btn-info">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> No invoices recorded for this supplier yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
