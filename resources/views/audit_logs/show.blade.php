@extends('layouts.app')

@section('main-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-history"></i> Audit Log Details #{{ $auditLog->id }}
                    </h3>
                    <div class="box-tools pull-right">
                        <a href="{{ route('audit-logs.index') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="box-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h4 class="text-primary"><i class="fa fa-info-circle"></i> Basic Information</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">ID</th>
                                    <td>{{ $auditLog->id }}</td>
                                </tr>
                                <tr>
                                    <th>Date/Time</th>
                                    <td>
                                        {{ $auditLog->created_at->format('Y-m-d H:i:s') }}
                                        <br>
                                        <small class="text-muted">({{ $auditLog->created_at->diffForHumans() }})</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>User</th>
                                    <td>
                                        @if($auditLog->user)
                                            <strong>{{ $auditLog->user->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $auditLog->user->email }}</small>
                                            <br>
                                            <a href="{{ route('audit-logs.index', ['user_id' => $auditLog->user_id]) }}" class="btn btn-sm btn-info">
                                                <i class="fa fa-search"></i> View all logs by this user
                                            </a>
                                        @else
                                            <span class="text-muted">System</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Action</th>
                                    <td>
                                        @php
                                            $actionBadgeClass = [
                                                'create' => 'success',
                                                'update' => 'info',
                                                'delete' => 'danger',
                                                'login' => 'primary',
                                                'logout' => 'default',
                                                'failed_login' => 'warning',
                                            ][$auditLog->action] ?? 'default';
                                        @endphp
                                        <span class="label label-{{ $actionBadgeClass }}" style="font-size: 14px;">
                                            {{ $auditLog->action_name }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Event Type</th>
                                    <td>
                                        @php
                                            $eventTypeBadgeClass = [
                                                'model' => 'primary',
                                                'auth' => 'success',
                                                'system' => 'warning',
                                            ][$auditLog->event_type] ?? 'default';
                                        @endphp
                                        <span class="label label-{{ $eventTypeBadgeClass }}" style="font-size: 14px;">
                                            {{ ucfirst($auditLog->event_type) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Model & Request Information -->
                        <div class="col-md-6">
                            <h4 class="text-primary"><i class="fa fa-database"></i> Model & Request Information</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Model Type</th>
                                    <td>
                                        @if($auditLog->model_type)
                                            <span class="badge bg-purple">{{ $auditLog->model_name }}</span>
                                            <br>
                                            <small class="text-muted">{{ $auditLog->model_type }}</small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Model ID</th>
                                    <td>
                                        @if($auditLog->model_id)
                                            <strong>#{{ $auditLog->model_id }}</strong>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>IP Address</th>
                                    <td>
                                        <code>{{ $auditLog->ip_address ?? 'N/A' }}</code>
                                    </td>
                                </tr>
                                <tr>
                                    <th>User Agent</th>
                                    <td>
                                        <small>{{ $auditLog->user_agent ?? 'N/A' }}</small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-12">
                            <h4 class="text-primary"><i class="fa fa-align-left"></i> Description</h4>
                            <div class="well">
                                {{ $auditLog->description ?? 'No description available' }}
                            </div>
                        </div>
                    </div>

                    <!-- Changes (Old vs New Values) -->
                    @if($auditLog->old_values || $auditLog->new_values)
                        <div class="row" style="margin-top: 20px;">
                            <div class="col-md-12">
                                <h4 class="text-primary"><i class="fa fa-exchange"></i> Changes</h4>
                                
                                @php
                                    $changes = $auditLog->changes;
                                @endphp

                                @if(!empty($changes))
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th width="25%">Field</th>
                                                    <th width="37.5%">Old Value</th>
                                                    <th width="37.5%">New Value</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($changes as $field => $values)
                                                    <tr>
                                                        <td><strong>{{ ucfirst(str_replace('_', ' ', $field)) }}</strong></td>
                                                        <td>
                                                            <span class="text-danger">
                                                                @if(is_null($values['old']))
                                                                    <em class="text-muted">null</em>
                                                                @elseif(is_bool($values['old']))
                                                                    {{ $values['old'] ? 'true' : 'false' }}
                                                                @elseif(is_array($values['old']))
                                                                    <pre>{{ json_encode($values['old'], JSON_PRETTY_PRINT) }}</pre>
                                                                @else
                                                                    {{ $values['old'] }}
                                                                @endif
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="text-success">
                                                                @if(is_null($values['new']))
                                                                    <em class="text-muted">null</em>
                                                                @elseif(is_bool($values['new']))
                                                                    {{ $values['new'] ? 'true' : 'false' }}
                                                                @elseif(is_array($values['new']))
                                                                    <pre>{{ json_encode($values['new'], JSON_PRETTY_PRINT) }}</pre>
                                                                @else
                                                                    {{ $values['new'] }}
                                                                @endif
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <!-- Show raw JSON if changes can't be parsed -->
                                    <div class="row">
                                        @if($auditLog->old_values)
                                            <div class="col-md-6">
                                                <h5>Old Values:</h5>
                                                <pre>{{ json_encode(json_decode($auditLog->old_values, true), JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        @endif
                                        @if($auditLog->new_values)
                                            <div class="col-md-6">
                                                <h5>New Values:</h5>
                                                <pre>{{ json_encode(json_decode($auditLog->new_values, true), JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Related Logs -->
                    @if($auditLog->model_type && $auditLog->model_id)
                        <div class="row" style="margin-top: 20px;">
                            <div class="col-md-12">
                                <h4 class="text-primary"><i class="fa fa-link"></i> Related Logs</h4>
                                <a href="{{ route('audit-logs.index', ['model_type' => class_basename($auditLog->model_type), 'model_id' => $auditLog->model_id]) }}" 
                                   class="btn btn-info">
                                    <i class="fa fa-search"></i> View all logs for this {{ $auditLog->model_name }} #{{ $auditLog->model_id }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="box-footer">
                    <a href="{{ route('audit-logs.index') }}" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
