@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'PC Specification Details',
    'subtitle' => $pcspec->cpu,
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'PC Specifications', 'url' => url('pcspecs')],
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

            {{-- PC Spec Metadata --}}
            <div class="box box-warning">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong><i class="fa fa-calendar"></i> Created:</strong> 
                            {{ $pcspec->created_at ? $pcspec->created_at->format('M d, Y h:i A') : 'N/A' }}
                        </div>
                        <div class="col-md-4">
                            <strong><i class="fa fa-clock"></i> Last Updated:</strong> 
                            {{ $pcspec->updated_at ? $pcspec->updated_at->format('M d, Y h:i A') : 'N/A' }}
                        </div>
                        <div class="col-md-4">
                            <strong><i class="fa fa-tag"></i> ID:</strong> 
                            #{{ $pcspec->id }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- PC Specification Details --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-microchip"></i> Hardware Specifications</h3>
                </div>
                <div class="box-body">
                    <fieldset>
                        <legend><span class="form-section-icon"><i class="fa fa-info-circle"></i></span> Component Details</legend>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><i class="fa fa-microchip"></i> CPU / Processor:</label>
                                    <p class="form-control-static" style="font-size: 16px; font-weight: bold; color: #333;">
                                        {{ $pcspec->cpu }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-memory"></i> RAM / Memory:</label>
                                    <p class="form-control-static" style="font-size: 16px; font-weight: bold; color: #333;">
                                        {{ $pcspec->ram }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-hdd"></i> Storage (HDD/SSD):</label>
                                    <p class="form-control-static" style="font-size: 16px; font-weight: bold; color: #333;">
                                        {{ $pcspec->hdd }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>

            {{-- Performance Category --}}
            @php
                $performanceCategory = 'Unknown';
                $categoryColor = 'bg-gray';
                $categoryIcon = 'fa-question';
                
                // Simple categorization based on specs
                $cpuLower = strtolower($pcspec->cpu);
                $ramLower = strtolower($pcspec->ram);
                
                if (str_contains($cpuLower, 'i9') || str_contains($cpuLower, 'xeon') || str_contains($ramLower, '32') || str_contains($ramLower, '64')) {
                    $performanceCategory = 'Workstation';
                    $categoryColor = 'bg-purple';
                    $categoryIcon = 'fa-rocket';
                } elseif (str_contains($cpuLower, 'i7') || str_contains($ramLower, '16')) {
                    $performanceCategory = 'Performance';
                    $categoryColor = 'bg-green';
                    $categoryIcon = 'fa-bolt';
                } elseif (str_contains($cpuLower, 'i5') || str_contains($ramLower, '8')) {
                    $performanceCategory = 'Standard';
                    $categoryColor = 'bg-blue';
                    $categoryIcon = 'fa-desktop';
                } elseif (str_contains($cpuLower, 'i3') || str_contains($ramLower, '4')) {
                    $performanceCategory = 'Basic';
                    $categoryColor = 'bg-aqua';
                    $categoryIcon = 'fa-laptop';
                }
            @endphp

            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-chart-line"></i> Performance Category</h3>
                </div>
                <div class="box-body">
                    <div class="info-box {{ $categoryColor }}" style="min-height: 90px;">
                        <span class="info-box-icon"><i class="fa {{ $categoryIcon }}"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Estimated Category</span>
                            <span class="info-box-number">{{ $performanceCategory }}</span>
                            <span class="progress-description">
                                Based on CPU and RAM specifications
                            </span>
                        </div>
                    </div>

                    <hr>

                    <p style="font-size: 13px; margin-bottom: 10px;"><strong><i class="fa fa-info-circle text-info"></i> Category Guidelines:</strong></p>
                    <ul style="margin-left: 20px; font-size: 12px;">
                        <li><strong>Basic:</strong> i3 / 4-8GB - General office work, email, web browsing</li>
                        <li><strong>Standard:</strong> i5 / 8-16GB - Multitasking, light design, business applications</li>
                        <li><strong>Performance:</strong> i7 / 16-32GB - Heavy multitasking, CAD, video editing</li>
                        <li><strong>Workstation:</strong> i9/Xeon / 32GB+ - Professional workloads, rendering, simulations</li>
                    </ul>
                </div>
            </div>

            {{-- Related Asset Models --}}
            @php
                $assetModels = \App\AssetModel::where('pcspec_id', $pcspec->id)->get();
                $totalAssets = 0;
                foreach($assetModels as $model) {
                    $totalAssets += \App\Asset::where('model_id', $model->id)->count();
                }
            @endphp

            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-boxes"></i> Asset Models Using This Specification</h3>
                    <span class="count-badge">{{ $assetModels->count() }}</span>
                </div>
                <div class="box-body">
                    @if($assetModels->count() > 0)
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Model Name</th>
                                    <th>Manufacturer</th>
                                    <th>Category</th>
                                    <th style="width: 100px; text-align: center;">Assets</th>
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
                                        <td>{{ $model->manufacturer->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($model->asset_type)
                                                <span class="badge bg-blue">{{ $model->asset_type->name }}</span>
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
                                    <td colspan="3" style="text-align: right;">Total Assets:</td>
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
                            <p>No asset models using this specification.</p>
                            <p class="text-muted" style="font-size: 12px;">This specification can be assigned to asset models.</p>
                        </div>
                    @endif
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
                    <a href="{{ url('pcspecs/' . $pcspec->id . '/edit') }}" class="btn btn-primary btn-block">
                        <i class="fa fa-edit"></i> Edit Specification
                    </a>
                    <a href="{{ url('pcspecs') }}" class="btn btn-default btn-block">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                    <hr>
                    <form method="POST" action="{{ url('pcspecs/' . $pcspec->id) }}" onsubmit="return confirm('Are you sure you want to delete this specification? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fa fa-trash"></i> Delete Specification
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
                                Using this spec
                            </span>
                        </div>
                    </div>

                    <div class="info-box bg-green" style="min-height: 80px; margin-bottom: 0;">
                        <span class="info-box-icon"><i class="fa fa-laptop"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Assets</span>
                            <span class="info-box-number">{{ $totalAssets }}</span>
                            <span class="progress-description">
                                With this spec
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Specification Summary --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-list"></i> Quick Summary</h3>
                </div>
                <div class="box-body">
                    <table class="table table-condensed" style="font-size: 12px; margin-bottom: 0;">
                        <tr>
                            <td><strong><i class="fa fa-microchip"></i> CPU:</strong></td>
                            <td>{{ Str::limit($pcspec->cpu, 20) }}</td>
                        </tr>
                        <tr>
                            <td><strong><i class="fa fa-memory"></i> RAM:</strong></td>
                            <td>{{ $pcspec->ram }}</td>
                        </tr>
                        <tr>
                            <td><strong><i class="fa fa-hdd"></i> Storage:</strong></td>
                            <td>{{ $pcspec->hdd }}</td>
                        </tr>
                        <tr>
                            <td><strong><i class="fa fa-chart-line"></i> Category:</strong></td>
                            <td><span class="badge {{ $categoryColor }}">{{ $performanceCategory }}</span></td>
                        </tr>
                    </table>
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
                            <a href="{{ url('models') }}">
                                <i class="fa fa-boxes text-primary"></i> View All Asset Models
                            </a>
                        </li>
                        @if($assetModels->count() > 0)
                        <li style="margin-bottom: 10px;">
                            <a href="{{ url('models?pcspec=' . $pcspec->id) }}">
                                <i class="fa fa-filter text-warning"></i> Filter Models by This Spec
                            </a>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <a href="{{ url('assets') }}">
                                <i class="fa fa-laptop text-success"></i> View All Assets
                            </a>
                        </li>
                        @endif
                        <li style="margin-bottom: 10px;">
                            <a href="{{ url('pcspecs') }}">
                                <i class="fa fa-microchip text-info"></i> View All Specifications
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Info Box --}}
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Information</h3>
                </div>
                <div class="box-body">
                    <p style="font-size: 13px;">
                        <i class="fa fa-lightbulb text-warning"></i> <strong>About PC Specifications:</strong>
                    </p>
                    <p style="font-size: 12px; color: #666;">
                        PC specifications define the hardware configuration for computer assets. They are linked to asset models and help track inventory by performance category.
                    </p>
                    <hr>
                    <p style="font-size: 12px; color: #666; margin-bottom: 0;">
                        <i class="fa fa-exclamation-triangle text-danger"></i> 
                        <strong>Warning:</strong> Deleting this specification may affect related asset models.
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
