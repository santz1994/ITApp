@extends('layouts.app')

@section('main-content')

@include('components.page-header', [
    'title' => 'Asset Model Details',
    'subtitle' => optional($asset_model->manufacturer)->name . ' ' . $asset_model->asset_model,
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('admin.dashboard'), 'icon' => 'home'],
        ['label' => 'Asset Models', 'url' => route('models.index')],
        ['label' => 'Details']
    ],
    'actions' => '
        <div class="btn-group" role="group">
            <a href="'.route('models.edit', $asset_model->id).'" class="btn btn-warning">
                <i class="fa fa-edit"></i> <span class="hidden-xs">Edit</span>
            </a>
            <a href="'.route('models.index').'" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> <span class="hidden-xs">Back</span>
            </a>
        </div>
    '
])

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            {{-- Model Information --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Model Information</h3>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        <dt>Manufacturer:</dt>
                        <dd>{{ optional($asset_model->manufacturer)->name ?? '-' }}</dd>

                        <dt>Model Name:</dt>
                        <dd><strong>{{ $asset_model->asset_model }}</strong></dd>

                        <dt>Asset Type:</dt>
                        <dd>{{ optional($asset_model->asset_type)->type_name ?? '-' }}</dd>

                        <dt>PC Specification:</dt>
                        <dd>
                            @if($asset_model->pcspec)
                                <strong>CPU:</strong> {{ $asset_model->pcspec->cpu }}<br>
                                <strong>RAM:</strong> {{ $asset_model->pcspec->ram }}<br>
                                <strong>HDD:</strong> {{ $asset_model->pcspec->hdd }}
                            @else
                                -
                            @endif
                        </dd>

                        <dt>Created:</dt>
                        <dd>{{ $asset_model->created_at ? $asset_model->created_at->format('d M Y H:i') : '-' }}</dd>

                        <dt>Last Updated:</dt>
                        <dd>{{ $asset_model->updated_at ? $asset_model->updated_at->format('d M Y H:i') : '-' }}</dd>
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

                    <div class="info-box bg-green">
                        <span class="info-box-icon"><i class="fa fa-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Deployed</span>
                            <span class="info-box-number">{{ $assets->where('status', 'deployed')->count() }}</span>
                        </div>
                    </div>

                    <div class="info-box bg-yellow">
                        <span class="info-box-icon"><i class="fa fa-wrench"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">In Repairs</span>
                            <span class="info-box-number">{{ $assets->where('status', 'repairs')->count() }}</span>
                        </div>
                    </div>

                    <div class="info-box bg-blue">
                        <span class="info-box-icon"><i class="fa fa-plus-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Ready to Deploy</span>
                            <span class="info-box-number">{{ $assets->where('status', 'ready')->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            {{-- Assets Using This Model --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-list"></i> Assets Using This Model</h3>
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
                                    <th>Serial Number</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                    <th>Location</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assets as $asset)
                                <tr>
                                    <td><strong>{{ $asset->asset_tag }}</strong></td>
                                    <td>{{ $asset->serial_number ?? '-' }}</td>
                                    <td>
                                        <span class="label label-{{ $asset->status == 'deployed' ? 'success' : ($asset->status == 'ready' ? 'info' : ($asset->status == 'repairs' ? 'warning' : 'default')) }}">
                                            {{ ucfirst($asset->status) }}
                                        </span>
                                    </td>
                                    <td>{{ optional($asset->assignedUser)->name ?? 'Unassigned' }}</td>
                                    <td>{{ optional($asset->location)->location_name ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('assets.show', $asset->id) }}" class="btn btn-xs btn-info">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                        <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-xs btn-warning">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> No assets are currently using this model.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
