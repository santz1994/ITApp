@extends('layouts.app')

@section('main-content')
    {{-- Modern Page Header --}}
    @include('components.page-header', [
        'title' => 'System Settings',
        'subtitle' => 'Configure system parameters and view information',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'System Settings', 'url' => null]
        ],
        'actions' => '
            <a href="'.route('system.maintenance').'" class="btn btn-warning">
                <i class="fa fa-wrench"></i> Maintenance
            </a>
            <a href="'.route('system.logs').'" class="btn btn-default">
                <i class="fa fa-file-text"></i> Logs
            </a>
        '
    ])

    <section class="content container-fluid">
        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif

        {{-- Quick Stats Dashboard --}}
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>{{ $systemInfo['php_version'] }}</h3>
                        <p>PHP Version</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-code"></i>
                    </div>
                    <a href="#system-info" class="small-box-footer">
                        More info <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{{ $systemInfo['laravel_version'] }}</h3>
                        <p>Laravel Version</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-rocket"></i>
                    </div>
                    <a href="#system-info" class="small-box-footer">
                        More info <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3>{{ ucfirst($systemInfo['cache_driver']) }}</h3>
                        <p>Cache Driver</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-tachometer"></i>
                    </div>
                    <a href="#system-status" class="small-box-footer">
                        View Status <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>{{ ucfirst($systemInfo['database_connection']) }}</h3>
                        <p>Database</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-database"></i>
                    </div>
                    <a href="#system-status" class="small-box-footer">
                        Connection Info <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- System Information --}}
            <div class="col-md-6" id="system-info">

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-info-circle"></i> System Information
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">
                            <i class="fa fa-server"></i> Technical details about your system configuration
                        </p>
                        <table class="table table-hover table-striped">
                            <tbody>
                                <tr>
                                    <td style="width: 50%;">
                                        <i class="fa fa-cube text-primary"></i> <strong>Application Version</strong>
                                    </td>
                                    <td>
                                        <span class="label label-primary">{{ $systemInfo['app_version'] }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="fa fa-rocket text-success"></i> <strong>Laravel Version</strong>
                                    </td>
                                    <td>
                                        <span class="label label-success">{{ $systemInfo['laravel_version'] }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="fa fa-code text-info"></i> <strong>PHP Version</strong>
                                    </td>
                                    <td>
                                        <span class="label label-info">{{ $systemInfo['php_version'] }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="fa fa-database text-danger"></i> <strong>Database</strong>
                                    </td>
                                    <td>
                                        <span class="label label-danger">{{ ucfirst($systemInfo['database_connection']) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="fa fa-tachometer text-warning"></i> <strong>Cache Driver</strong>
                                    </td>
                                    <td>
                                        <span class="label label-warning">{{ ucfirst($systemInfo['cache_driver']) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="fa fa-key text-default"></i> <strong>Session Driver</strong>
                                    </td>
                                    <td>
                                        <span class="label label-default">{{ ucfirst($systemInfo['session_driver']) }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="col-md-6">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-bolt"></i> Quick Actions
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">
                            <i class="fa fa-rocket"></i> Commonly used system management tools
                        </p>
                        <div class="list-group">
                            <a href="{{ route('system.permissions') }}" class="list-group-item">
                                <span class="badge bg-primary"><i class="fa fa-key"></i></span>
                                <h4 class="list-group-item-heading">Manage Permissions</h4>
                                <p class="list-group-item-text text-muted">Configure system permissions and access control</p>
                            </a>
                            <a href="{{ route('system.roles') }}" class="list-group-item">
                                <span class="badge bg-info"><i class="fa fa-users"></i></span>
                                <h4 class="list-group-item-heading">Manage Roles</h4>
                                <p class="list-group-item-text text-muted">Define and assign user roles</p>
                            </a>
                            <a href="{{ route('system.maintenance') }}" class="list-group-item">
                                <span class="badge bg-warning"><i class="fa fa-wrench"></i></span>
                                <h4 class="list-group-item-heading">System Maintenance</h4>
                                <p class="list-group-item-text text-muted">Perform system maintenance tasks</p>
                            </a>
                            <a href="{{ route('system.logs') }}" class="list-group-item">
                                <span class="badge bg-default"><i class="fa fa-file-text"></i></span>
                                <h4 class="list-group-item-heading">View System Logs</h4>
                                <p class="list-group-item-text text-muted">Monitor application logs and errors</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- System Status --}}
        <div class="row" id="system-status">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-heartbeat"></i> System Status Monitor
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">
                            <i class="fa fa-check-circle text-success"></i> Real-time status of system components
                        </p>
                        <div class="row">
                            <div class="col-lg-3 col-md-6">
                                <div class="info-box bg-green">
                                    <span class="info-box-icon">
                                        <i class="fa fa-database"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Database</span>
                                        <span class="info-box-number">
                                            <i class="fa fa-check-circle"></i> Online
                                        </span>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: 100%"></div>
                                        </div>
                                        <span class="progress-description">
                                            {{ ucfirst($systemInfo['database_connection']) }} Connection
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <div class="info-box bg-aqua">
                                    <span class="info-box-icon">
                                        <i class="fa fa-tachometer"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Cache</span>
                                        <span class="info-box-number">
                                            <i class="fa fa-check-circle"></i> Active
                                        </span>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: 100%"></div>
                                        </div>
                                        <span class="progress-description">
                                            {{ ucfirst($systemInfo['cache_driver']) }} Driver
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <div class="info-box bg-yellow">
                                    <span class="info-box-icon">
                                        <i class="fa fa-list"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Queue</span>
                                        <span class="info-box-number">
                                            <i class="fa fa-check-circle"></i> Running
                                        </span>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: 100%"></div>
                                        </div>
                                        <span class="progress-description">
                                            {{ ucfirst($systemInfo['queue_driver']) }} Connection
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <div class="info-box bg-red">
                                    <span class="info-box-icon">
                                        <i class="fa fa-lock"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Session</span>
                                        <span class="info-box-number">
                                            <i class="fa fa-check-circle"></i> Secured
                                        </span>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: 100%"></div>
                                        </div>
                                        <span class="progress-description">
                                            {{ ucfirst($systemInfo['session_driver']) }} Storage
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<style>
.list-group-item {
    transition: all 0.3s ease;
}

.list-group-item:hover {
    background-color: #f5f5f5;
    transform: translateX(5px);
}

.list-group-item .badge {
    font-size: 18px;
    padding: 10px;
    border-radius: 3px;
}

.list-group-item-heading {
    color: #333;
    margin-top: 0;
    margin-bottom: 5px;
}

.list-group-item-text {
    font-size: 12px;
}
</style>
@endsection
