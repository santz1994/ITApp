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
          <h3 class="box-title">Settings</h3>
        </div>
        <div class="box-body">
          <a href="{{ route('system-settings.index') }}" class="btn btn-app">
            <i class="fa fa-cog"></i> System Settings
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
