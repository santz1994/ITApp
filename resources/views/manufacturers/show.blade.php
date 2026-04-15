@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Manufacturer Details',
    'subtitle' => $manufacturer->name,
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Manufacturers', 'url' => url('manufacturers')],
        ['label' => 'Details']
    ]
])

<div class="container-fluid">
    <div class="row">
        {{-- Main Content --}}
        <div class="col-md-9">
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

            {{-- Manufacturer Metadata --}}
            <div class="box box-warning">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong><i class="fa fa-calendar"></i> Created:</strong> 
                            {{ $manufacturer->created_at ? $manufacturer->created_at->format('M d, Y h:i A') : 'N/A' }}
                        </div>
                        <div class="col-md-4">
                            <strong><i class="fa fa-clock"></i> Last Updated:</strong> 
                            {{ $manufacturer->updated_at ? $manufacturer->updated_at->format('M d, Y h:i A') : 'N/A' }}
                        </div>
                        <div class="col-md-4">
                            <strong><i class="fa fa-tag"></i> ID:</strong> 
                            #{{ $manufacturer->id }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Manufacturer Details --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-industry"></i> Manufacturer Information</h3>
                </div>
                <div class="box-body">
                    <fieldset>
                        <legend><span class="form-section-icon"><i class="fa fa-info-circle"></i></span> Basic Details</legend>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><i class="fa fa-industry"></i> Manufacturer Name:</label>
                                    <p class="form-control-static" style="font-size: 18px; font-weight: bold; color: #333;">
                                        {{ $manufacturer->name }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>

            {{-- Related Asset Models --}}
            @php
                // Get asset models for this manufacturer
                $assetModels = \App\AssetModel::where('manufacturer_id', $manufacturer->id)->get();
                $totalAssets = 0;
                foreach($assetModels as $model) {
                    $totalAssets += \App\Asset::where('model_id', $model->id)->count();
                }
            @endphp

            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-boxes"></i> Related Asset Models</h3>
                    <span class="count-badge">{{ $assetModels->count() }}</span>
                </div>
                <div class="box-body">
                    @if($assetModels->count() > 0)
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Model Name</th>
                                    <th>Category</th>
                                    <th style="width: 100px; text-align: center;">Assets Count</th>
                                    <th style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assetModels as $model)
                                    @php
                                        $assetCount = \App\Asset::where('model_id', $model->id)->count();
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $model->name }}</strong></td>
                                        <td>
                                            @if($model->category)
                                                <span class="badge bg-blue">{{ $model->category }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="badge bg-green">{{ $assetCount }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ url('models/' . $model->id . '/edit') }}" class="btn btn-sm btn-primary" title="Edit Model">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #f9f9f9; font-weight: bold;">
                                    <td colspan="2" style="text-align: right;">Total Assets Across All Models:</td>
                                    <td style="text-align: center;">
                                        <span class="badge bg-blue" style="font-size: 14px;">{{ $totalAssets }}</span>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    @else
                        <div class="text-center empty-state" style="padding: 30px;">
                            <i class="fa fa-boxes fa-3x" style="opacity: 0.3; margin-bottom: 15px;"></i>
                            <p>No asset models found for this manufacturer.</p>
                            <p class="text-muted" style="font-size: 12px;">Create an asset model to link it to this manufacturer.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Activity Timeline (if available) --}}
            @if($manufacturer->created_at || $manufacturer->updated_at)
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-history"></i> Timeline</h3>
                </div>
                <div class="box-body">
                    <ul class="timeline">
                        @if($manufacturer->updated_at && $manufacturer->created_at && !$manufacturer->created_at->eq($manufacturer->updated_at))
                        <li>
                            <i class="fa fa-edit bg-blue"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fa fa-clock"></i> {{ $manufacturer->updated_at->diffForHumans() }}</span>
                                <h3 class="timeline-header">Last Updated</h3>
                                <div class="timeline-body">
                                    Manufacturer information was last modified on {{ $manufacturer->updated_at->format('F d, Y \a\t h:i A') }}.
                                </div>
                            </div>
                        </li>
                        @endif
                        
                        @if($manufacturer->created_at)
                        <li>
                            <i class="fa fa-plus bg-green"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fa fa-clock"></i> {{ $manufacturer->created_at->diffForHumans() }}</span>
                                <h3 class="timeline-header">Created</h3>
                                <div class="timeline-body">
                                    Manufacturer was created on {{ $manufacturer->created_at->format('F d, Y \a\t h:i A') }}.
                                </div>
                            </div>
                        </li>
                        @endif
                        
                        <li>
                            <i class="fa fa-clock bg-gray"></i>
                        </li>
                    </ul>
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
                    <a href="{{ url('manufacturers/' . $manufacturer->id . '/edit') }}" class="btn btn-primary btn-block">
                        <i class="fa fa-edit"></i> Edit Manufacturer
                    </a>
                    <a href="{{ url('manufacturers') }}" class="btn btn-default btn-block">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                    <hr>
                    <form method="POST" action="{{ url('manufacturers/' . $manufacturer->id) }}" onsubmit="return confirm('Are you sure you want to delete this manufacturer? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fa fa-trash"></i> Delete Manufacturer
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
                    <div class="info-box bg-aqua" style="min-height: 80px; margin-bottom: 15px;">
                        <span class="info-box-icon"><i class="fa fa-boxes"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Asset Models</span>
                            <span class="info-box-number">{{ $assetModels->count() }}</span>
                            <span class="progress-description">
                                Different models
                            </span>
                        </div>
                    </div>

                    <div class="info-box bg-green" style="min-height: 80px; margin-bottom: 0;">
                        <span class="info-box-icon"><i class="fa fa-laptop"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Assets</span>
                            <span class="info-box-number">{{ $totalAssets }}</span>
                            <span class="progress-description">
                                Across all models
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Related Links --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-link"></i> Related Links</h3>
                </div>
                <div class="box-body">
                    <ul style="list-style: none; padding-left: 0;">
                        <li style="margin-bottom: 10px;">
                            <a href="{{ url('models') }}">
                                <i class="fa fa-boxes text-primary"></i> View All Asset Models
                            </a>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <a href="{{ url('assets') }}">
                                <i class="fa fa-laptop text-success"></i> View All Assets
                            </a>
                        </li>
                        @if($assetModels->count() > 0)
                        <li style="margin-bottom: 10px;">
                            <a href="{{ url('models?manufacturer=' . $manufacturer->id) }}">
                                <i class="fa fa-filter text-warning"></i> Filter Models by This Manufacturer
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            {{-- Info Box --}}
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Information</h3>
                </div>
                <div class="box-body">
                    <p style="font-size: 13px;">
                        <i class="fa fa-lightbulb text-warning"></i> <strong>About Manufacturers:</strong>
                    </p>
                    <p style="font-size: 12px; color: #666;">
                        Manufacturers are linked to asset models, which in turn are linked to individual assets. 
                        Deleting a manufacturer may affect associated models and assets.
                    </p>
                    <hr>
                    <p style="font-size: 12px; color: #666; margin-bottom: 0;">
                        <i class="fa fa-exclamation-triangle text-danger"></i> 
                        <strong>Warning:</strong> Always verify relationships before deletion.
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
