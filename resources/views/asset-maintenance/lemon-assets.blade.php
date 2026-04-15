@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-lemon-o"></i> Lemon Assets
                    </h3>
                    <div class="box-tools pull-right">
                        <a href="{{ route('asset-maintenance.index') }}" class="btn btn-sm btn-default">
                            <i class="fa fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-warning">
                                <h4><i class="icon fa fa-warning"></i> Lemon Assets</h4>
                                These assets have had 3 or more maintenance tickets in the last month and may require replacement or intensive maintenance.
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box bg-yellow">
                                <span class="info-box-icon"><i class="fa fa-lemon-o"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Lemon Assets</span>
                                    <span class="info-box-number">{{ $lemonStats['total'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-red">
                                <span class="info-box-icon"><i class="fa fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">High Priority</span>
                                    <span class="info-box-number">{{ $lemonStats['high_priority'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-orange">
                                <span class="info-box-icon"><i class="fa fa-wrench"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Avg Tickets/Asset</span>
                                    <span class="info-box-number">{{ $lemonStats['avg_tickets'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-purple">
                                <span class="info-box-icon"><i class="fa fa-dollar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Est. Replacement Cost</span>
                                    <span class="info-box-number">${{ number_format($lemonStats['replacement_cost'] ?? 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-striped table-hover" id="lemon-assets-table">
                                <thead>
                                    <tr>
                                        <th>Asset Tag</th>
                                        <th>Model</th>
                                        <th>Serial Number</th>
                                        <th>Status</th>
                                        <th>Tickets (30 days)</th>
                                        <th>Last Ticket</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($lemonAssets as $asset)
                                    <tr>
                                        <td>
                                            <a href="{{ route('assets.show', $asset->id) }}">
                                                {{ $asset->asset_tag }}
                                            </a>
                                        </td>
                                        <td>{{ $asset->model->asset_model ?? 'Unknown' }}</td>
                                        <td>{{ $asset->serial_number }}</td>
                                        <td>
                                            <span class="label label-{{ $asset->status->color ?? 'default' }}">
                                                {{ $asset->status->name ?? 'Unknown' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-red">{{ $asset->recent_tickets_count }}</span>
                                        </td>
                                        <td>{{ $asset->last_ticket_date ? $asset->last_ticket_date->format('Y-m-d') : 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('asset-maintenance.show', $asset->id) }}" class="btn btn-sm btn-info">
                                                <i class="fa fa-history"></i> History
                                            </a>
                                            <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fa fa-edit"></i> Edit
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            <i class="fa fa-smile-o"></i> No lemon assets found! All assets are performing well.
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
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#lemon-assets-table').DataTable({
        "order": [[ 4, "desc" ]],
        "pageLength": 25
    });
});
</script>
@endpush
@endsection
