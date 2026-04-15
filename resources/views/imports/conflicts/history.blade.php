@extends('layouts.app')

@section('main-content')

@include('components.page-header', [
    'title' => 'Resolution History',
    'subtitle' => 'View all conflict resolutions and audit trail',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Master Data', 'url' => route('masterdata.index')],
        ['label' => 'Imports', 'url' => route('masterdata.imports')],
        ['label' => 'Conflicts', 'url' => route('imports.conflicts.index', $import->import_id)],
        ['label' => 'History']
    ],
    'actions' => '
        <a href="'.route('imports.conflicts.index', $import->import_id).'" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> <span class="hidden-xs">Back</span>
        </a>
    '
])

<div class="row">
    <div class="col-md-12">
        <!-- Summary Statistics -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="info-box">
                    <span class="info-box-icon bg-blue"><i class="fa fa-history"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Resolutions</span>
                        <span class="info-box-number">{{ $resolutionHistory->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Completed</span>
                        <span class="info-box-number">{{ $resolutionHistory->where('choice', 'create_new')->count() + $resolutionHistory->where('choice', 'update_existing')->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box">
                    <span class="info-box-icon bg-orange"><i class="fa fa-forward"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Skipped</span>
                        <span class="info-box-number">{{ $resolutionHistory->where('choice', 'skip')->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box">
                    <span class="info-box-icon bg-purple"><i class="fa fa-compress"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Merged</span>
                        <span class="info-box-number">{{ $resolutionHistory->where('choice', 'merge')->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resolution Timeline -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-clock-o"></i> Resolution Timeline
                </h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                @if($resolutionHistory->count() > 0)
                    <div class="timeline">
                        @forelse($resolutionHistory as $index => $resolution)
                            <div class="time-label">
                                <span class="bg-red">
                                    {{ $resolution->created_at->format('M d, Y') }}
                                </span>
                            </div>
                            <div>
                                <i class="fa fa-check-circle bg-green"></i>
                                <div class="timeline-item">
                                    <span class="time">
                                        <i class="fa fa-clock-o"></i> 
                                        {{ $resolution->created_at->format('h:i A') }}
                                    </span>
                                    <h3 class="timeline-header">
                                        <strong>Conflict #{{ $resolution->conflict_id }}</strong> - Row {{ $resolution->conflict->row_number }}
                                    </h3>
                                    <div class="timeline-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <dl class="dl-horizontal">
                                                    <dt>Conflict Type:</dt>
                                                    <dd>
                                                        <span class="badge" style="background-color: @switch($resolution->conflict->conflict_type)
                                                            @case('duplicate_key') #DD4B39 @break
                                                            @case('duplicate_record') #F39C12 @break
                                                            @case('foreign_key_not_found') #3498DB @break
                                                            @case('invalid_data') #9B59B6 @break
                                                            @case('business_rule_violation') #E74C3C @break
                                                            @default #95A5A6
                                                        @endswitch">
                                                            {{ $resolution->conflict->getConflictTypeLabel() }}
                                                        </span>
                                                    </dd>
                                                    
                                                    <dt>Resolution:</dt>
                                                    <dd>
                                                        <span class="label @switch($resolution->choice)
                                                            @case('skip') label-warning @break
                                                            @case('create_new') label-success @break
                                                            @case('update_existing') label-info @break
                                                            @case('merge') label-primary @break
                                                            @default label-default
                                                        @endswitch">
                                                            {{ $resolution->getChoiceLabel() }}
                                                        </span>
                                                    </dd>
                                                </dl>
                                            </div>
                                            <div class="col-md-6">
                                                <dl class="dl-horizontal">
                                                    <dt>Resolved By:</dt>
                                                    <dd>
                                                        <a href="#">{{ $resolution->user->name }}</a>
                                                        <br>
                                                        <small class="text-muted">{{ $resolution->user->email }}</small>
                                                    </dd>
                                                    
                                                    <dt>Timestamp:</dt>
                                                    <dd>
                                                        {{ $resolution->created_at->format('M d, Y h:i A') }}
                                                        <br>
                                                        <small class="text-muted">{{ $resolution->created_at->diffForHumans() }}</small>
                                                    </dd>
                                                </dl>
                                            </div>
                                        </div>

                                        @if($resolution->choice_details)
                                            <div class="row" style="margin-top: 10px;">
                                                <div class="col-md-12">
                                                    <strong>Additional Details:</strong>
                                                    <div class="well well-sm" style="margin: 5px 0;">
                                                        <pre>{{ json_encode($resolution->choice_details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> No resolutions recorded yet
                            </div>
                        @endforelse
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> No resolution history available for this import
                    </div>
                @endif
            </div>
        </div>

        <!-- Users Summary -->
        @php
            $resolutionsByUser = $resolutionHistory->groupBy('user_id');
        @endphp
        @if($resolutionsByUser->count() > 0)
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-users"></i> Resolutions by User
                    </h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        @foreach($resolutionsByUser as $userId => $resolutions)
                            @php
                                $user = $resolutions->first()->user;
                            @endphp
                            <div class="col-md-6">
                                <div class="box-group" id="accordion">
                                    <div class="panel box box-primary">
                                        <div class="box-header with-border" data-toggle="collapse" data-parent="#accordion" href="#user-{{ $userId }}">
                                            <h4 class="box-title">
                                                <i class="fa fa-user-circle"></i> {{ $user->name }}
                                                <span class="badge bg-blue" style="margin-left: 10px;">{{ $resolutions->count() }}</span>
                                            </h4>
                                        </div>
                                        <div id="user-{{ $userId }}" class="panel-collapse collapse">
                                            <div class="box-body">
                                                <ul class="list-unstyled">
                                                    @foreach($resolutions->take(5) as $resolution)
                                                        <li>
                                                            <strong>Conflict #{{ $resolution->conflict_id }}</strong> (Row {{ $resolution->conflict->row_number }})
                                                            <br>
                                                            <span class="label @switch($resolution->choice)
                                                                @case('skip') label-warning @break
                                                                @case('create_new') label-success @break
                                                                @case('update_existing') label-info @break
                                                                @case('merge') label-primary @break
                                                                @default label-default
                                                            @endswitch">
                                                                {{ $resolution->getChoiceLabel() }}
                                                            </span>
                                                            <br>
                                                            <small class="text-muted">{{ $resolution->created_at->diffForHumans() }}</small>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                @if($resolutions->count() > 5)
                                                    <p class="text-center text-muted" style="margin-top: 10px;">
                                                        <small>and {{ $resolutions->count() - 5 }} more...</small>
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection

@section('styles')
<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 40px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #ddd;
}

.time-label {
    position: relative;
    margin: 10px 0 20px 0;
}

.time-label > span {
    position: relative;
    display: inline-block;
    background: #fff;
    padding: 5px 10px;
    border-radius: 4px;
}

.timeline > div {
    margin-bottom: 20px;
    margin-left: 80px;
    position: relative;
}

.timeline > div > i {
    position: absolute;
    left: -64px;
    top: 0;
    font-size: 24px;
    width: 50px;
    height: 50px;
    line-height: 50px;
    text-align: center;
    color: #fff;
    border-radius: 50%;
    background: #ddd;
}

.timeline-item {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    padding: 0;
    background: #fff;
    border-radius: 3px;
}

.timeline-header {
    margin: 0;
    padding: 10px;
    border-bottom: 1px solid #eee;
}

.timeline-body {
    padding: 10px;
}

.time-label {
    margin-top: 20px;
    margin-bottom: 10px;
}
</style>
@endsection
