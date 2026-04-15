@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Division Details',
    'subtitle' => $division->name,
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Divisions', 'url' => url('divisions')],
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

            {{-- Division Metadata --}}
            <div class="box box-warning">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong><i class="fa fa-calendar"></i> Created:</strong> 
                            {{ $division->created_at ? $division->created_at->format('M d, Y h:i A') : 'N/A' }}
                        </div>
                        <div class="col-md-4">
                            <strong><i class="fa fa-clock"></i> Last Updated:</strong> 
                            {{ $division->updated_at ? $division->updated_at->format('M d, Y h:i A') : 'N/A' }}
                        </div>
                        <div class="col-md-4">
                            <strong><i class="fa fa-tag"></i> ID:</strong> 
                            #{{ $division->id }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Division Details --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-sitemap"></i> Division Information</h3>
                </div>
                <div class="box-body">
                    <fieldset>
                        <legend><span class="form-section-icon"><i class="fa fa-info-circle"></i></span> Basic Details</legend>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><i class="fa fa-sitemap"></i> Division Name:</label>
                                    <p class="form-control-static" style="font-size: 18px; font-weight: bold; color: #333;">
                                        {{ $division->name }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>

            {{-- Related Users --}}
            @php
                $users = \App\User::where('division_id', $division->id)->get();
            @endphp

            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-users"></i> Users in This Division</h3>
                    <span class="count-badge">{{ $users->count() }}</span>
                </div>
                <div class="box-body">
                    @if($users->count() > 0)
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td><strong>{{ $user->name }}</strong></td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->phone ?? 'N/A' }}</td>
                                        <td>
                                            <a href="{{ url('users/' . $user->id . '/edit') }}" class="btn btn-sm btn-primary" title="Edit User">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center empty-state" style="padding: 30px;">
                            <i class="fa fa-users fa-3x" style="opacity: 0.3; margin-bottom: 15px;"></i>
                            <p>No users assigned to this division.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Related Assets --}}
            @php
                $assets = \App\Asset::where('division_id', $division->id)->get();
            @endphp

            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-laptop"></i> Assets in This Division</h3>
                    <span class="count-badge">{{ $assets->count() }}</span>
                </div>
                <div class="box-body">
                    @if($assets->count() > 0)
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Asset Tag</th>
                                    <th>Model</th>
                                    <th>Serial Number</th>
                                    <th>Status</th>
                                    <th style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assets->take(10) as $asset)
                                    <tr>
                                        <td><strong>{{ $asset->asset_tag }}</strong></td>
                                        <td>{{ $asset->model->name ?? 'N/A' }}</td>
                                        <td>{{ $asset->serial_number ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge {{ $asset->status->color ?? 'bg-gray' }}">
                                                {{ $asset->status->name ?? 'Unknown' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ url('assets/' . $asset->id . '/edit') }}" class="btn btn-sm btn-primary" title="Edit Asset">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            @if($assets->count() > 10)
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <a href="{{ url('assets?division=' . $division->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-eye"></i> View All {{ $assets->count() }} Assets
                                        </a>
                                    </td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    @else
                        <div class="text-center empty-state" style="padding: 30px;">
                            <i class="fa fa-laptop fa-3x" style="opacity: 0.3; margin-bottom: 15px;"></i>
                            <p>No assets assigned to this division.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Related Budgets --}}
            @php
                $budgets = \App\Budget::where('division_id', $division->id)->get();
            @endphp

            @if($budgets->count() > 0)
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-dollar-sign"></i> Division Budgets</h3>
                    <span class="count-badge">{{ $budgets->count() }}</span>
                </div>
                <div class="box-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Budget Name</th>
                                <th>Amount</th>
                                <th>Fiscal Year</th>
                                <th style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($budgets as $budget)
                                <tr>
                                    <td><strong>{{ $budget->name }}</strong></td>
                                    <td>${{ number_format($budget->amount, 2) }}</td>
                                    <td>{{ $budget->fiscal_year ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ url('budgets/' . $budget->id . '/edit') }}" class="btn btn-sm btn-primary" title="Edit Budget">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
                    <a href="{{ url('divisions/' . $division->id . '/edit') }}" class="btn btn-primary btn-block">
                        <i class="fa fa-edit"></i> Edit Division
                    </a>
                    <a href="{{ url('divisions') }}" class="btn btn-default btn-block">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                    <hr>
                    <form method="POST" action="{{ url('divisions/' . $division->id) }}" onsubmit="return confirm('Are you sure you want to delete this division? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fa fa-trash"></i> Delete Division
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
                        <span class="info-box-icon"><i class="fa fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Users</span>
                            <span class="info-box-number">{{ $users->count() }}</span>
                            <span class="progress-description">
                                Team members
                            </span>
                        </div>
                    </div>

                    <div class="info-box bg-green" style="min-height: 80px; margin-bottom: 15px;">
                        <span class="info-box-icon"><i class="fa fa-laptop"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Assets</span>
                            <span class="info-box-number">{{ $assets->count() }}</span>
                            <span class="progress-description">
                                Equipment assigned
                            </span>
                        </div>
                    </div>

                    @if($budgets->count() > 0)
                    <div class="info-box bg-yellow" style="min-height: 80px; margin-bottom: 0;">
                        <span class="info-box-icon"><i class="fa fa-dollar-sign"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Budget</span>
                            <span class="info-box-number">${{ number_format($budgets->sum('amount'), 0) }}</span>
                            <span class="progress-description">
                                Across {{ $budgets->count() }} budget(s)
                            </span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Related Links --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-link"></i> Related Links</h3>
                </div>
                <div class="box-body">
                    <ul style="list-style: none; padding-left: 0;">
                        @if($users->count() > 0)
                        <li style="margin-bottom: 10px;">
                            <a href="{{ url('users?division=' . $division->id) }}">
                                <i class="fa fa-users text-primary"></i> View All Users
                            </a>
                        </li>
                        @endif
                        @if($assets->count() > 0)
                        <li style="margin-bottom: 10px;">
                            <a href="{{ url('assets?division=' . $division->id) }}">
                                <i class="fa fa-laptop text-success"></i> View All Assets
                            </a>
                        </li>
                        @endif
                        @if($budgets->count() > 0)
                        <li style="margin-bottom: 10px;">
                            <a href="{{ url('budgets?division=' . $division->id) }}">
                                <i class="fa fa-dollar-sign text-warning"></i> View All Budgets
                            </a>
                        </li>
                        @endif
                        <li style="margin-bottom: 10px;">
                            <a href="{{ url('divisions') }}">
                                <i class="fa fa-sitemap text-info"></i> View All Divisions
                            </a>
                        </li>
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
                        <i class="fa fa-lightbulb text-warning"></i> <strong>About Divisions:</strong>
                    </p>
                    <p style="font-size: 12px; color: #666;">
                        Divisions represent organizational units or departments. They are used to categorize users, assets, and budgets.
                    </p>
                    <hr>
                    <p style="font-size: 12px; color: #666; margin-bottom: 0;">
                        <i class="fa fa-exclamation-triangle text-danger"></i> 
                        <strong>Warning:</strong> Deleting a division may affect related users and assets.
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
