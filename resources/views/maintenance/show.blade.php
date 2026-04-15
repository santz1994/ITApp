@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Maintenance Log Details</h3>
                    <div class="box-tools pull-right">
                        <a href="{{ route('maintenance.index') }}" class="btn btn-sm btn-default">
                            <i class="fa fa-arrow-left"></i> Back to Maintenance Logs
                        </a>
                        <a href="{{ route('maintenance.edit', $maintenanceLog->id) }}" class="btn btn-sm btn-primary">
                            <i class="fa fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <!-- Left Column: Asset Information -->
                        <div class="col-md-6">
                            <h4><i class="fa fa-laptop"></i> Asset Information</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Asset Tag:</th>
                                    <td>
                                        <a href="{{ route('assets.show', $maintenanceLog->asset->id) }}">
                                            {{ $maintenanceLog->asset->asset_tag }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Asset Name:</th>
                                    <td>{{ $maintenanceLog->asset->name }}</td>
                                </tr>
                                <tr>
                                    <th>Model:</th>
                                    <td>{{ $maintenanceLog->asset->model->asset_model ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Serial Number:</th>
                                    <td>{{ $maintenanceLog->asset->serial_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Location:</th>
                                    <td>{{ $maintenanceLog->asset->location->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Right Column: Maintenance Information -->
                        <div class="col-md-6">
                            <h4><i class="fa fa-wrench"></i> Maintenance Information</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Maintenance Date:</th>
                                    <td>{{ \Carbon\Carbon::parse($maintenanceLog->maintenance_date)->format('l, j F Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Maintenance Type:</th>
                                    <td>
                                        <span class="label label-{{ $maintenanceLog->maintenance_type === 'preventive' ? 'info' : 'warning' }}">
                                            {{ ucfirst($maintenanceLog->maintenance_type) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Performed By:</th>
                                    <td>{{ $maintenanceLog->performedBy->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Cost:</th>
                                    <td>
                                        @if($maintenanceLog->cost)
                                            Rp {{ number_format($maintenanceLog->cost, 2) }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($maintenanceLog->ticket_id)
                                <tr>
                                    <th>Related Ticket:</th>
                                    <td>
                                        <a href="{{ route('tickets.show', $maintenanceLog->ticket_id) }}">
                                            {{ $maintenanceLog->ticket->ticket_code ?? 'View Ticket' }}
                                        </a>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Full Width: Description -->
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-12">
                            <h4><i class="fa fa-file-text"></i> Description</h4>
                            <div class="well">
                                {!! nl2br(e($maintenanceLog->description ?? 'No description provided.')) !!}
                            </div>
                        </div>
                    </div>

                    <!-- Full Width: Notes -->
                    @if($maintenanceLog->notes)
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-12">
                            <h4><i class="fa fa-sticky-note"></i> Notes</h4>
                            <div class="well">
                                {!! nl2br(e($maintenanceLog->notes)) !!}
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Timestamps -->
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-12">
                            <small class="text-muted">
                                <i class="fa fa-clock-o"></i> Created: {{ $maintenanceLog->created_at->format('Y-m-d H:i:s') }}
                                @if($maintenanceLog->updated_at->ne($maintenanceLog->created_at))
                                    | Updated: {{ $maintenanceLog->updated_at->format('Y-m-d H:i:s') }}
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- File Attachments Section -->
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-paperclip"></i> Maintenance Attachments</h3>
                </div>
                <div class="box-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#before-photos" aria-controls="before-photos" role="tab" data-toggle="tab">
                                <i class="fa fa-camera"></i> Before Photos
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#after-photos" aria-controls="after-photos" role="tab" data-toggle="tab">
                                <i class="fa fa-camera"></i> After Photos
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#receipts" aria-controls="receipts" role="tab" data-toggle="tab">
                                <i class="fa fa-file-text"></i> Receipts
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" style="padding-top: 20px;">
                        <!-- Before Photos Tab -->
                        <div role="tabpanel" class="tab-pane active" id="before-photos">
                            @include('partials.file-uploader', [
                                'model_type' => 'maintenance_log',
                                'model_id' => $maintenanceLog->id,
                                'collection' => 'before_photos'
                            ])
                        </div>

                        <!-- After Photos Tab -->
                        <div role="tabpanel" class="tab-pane" id="after-photos">
                            @include('partials.file-uploader', [
                                'model_type' => 'maintenance_log',
                                'model_id' => $maintenanceLog->id,
                                'collection' => 'after_photos'
                            ])
                        </div>

                        <!-- Receipts Tab -->
                        <div role="tabpanel" class="tab-pane" id="receipts">
                            @include('partials.file-uploader', [
                                'model_type' => 'maintenance_log',
                                'model_id' => $maintenanceLog->id,
                                'collection' => 'receipts'
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
