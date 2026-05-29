@extends('layouts.app')

@section('main-content')
  <div class="row">
    <div class="col-md-3">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">User Management</h3>
        </div>
        <div class="box-body">
          <a href="{{ route('users.index') }}" class="btn btn-app">
            <i class="fa fa-users"></i> Users
          </a>
          <a href="{{ route('users.roles') }}" class="btn btn-app">
            <i class="fa fa-id-badge"></i> Roles
          </a>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">System</h3>
        </div>
        <div class="box-body">
          <a href="{{ route('system.settings') }}" class="btn btn-app">
            <i class="fa fa-server"></i> System Info
          </a>
          <a href="{{ route('system.permissions') }}" class="btn btn-app">
            <i class="fa fa-key"></i> Permissions
          </a>
          <a href="{{ route('system.roles') }}" class="btn btn-app">
            <i class="fa fa-users"></i> Roles
          </a>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">Database</h3>
        </div>
        <div class="box-body">
          <a href="{{ route('admin.database.index') }}" class="btn btn-app">
            <i class="fa fa-database"></i> Database
          </a>
          <a href="{{ route('admin.cache') }}" class="btn btn-app">
            <i class="fa fa-hdd-o"></i> Cache
          </a>
          <a href="{{ route('admin.backup') }}" class="btn btn-app">
            <i class="fa fa-cloud-download"></i> Backup
          </a>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">Tools</h3>
        </div>
        <div class="box-body">
          <a href="{{ route('admin.menus.index') }}" class="btn btn-app">
            <i class="fa fa-bars"></i> Menus
          </a>
          <a href="{{ route('notification-settings.index') }}" class="btn btn-app">
            <i class="fa fa-bell"></i> Notifications
          </a>
          <a href="{{ route('audit-logs.index') }}" class="btn btn-app">
            <i class="fa fa-history"></i> Audit Logs
          </a>
        </div>
      </div>
    </div>
  </div>
@endsection
