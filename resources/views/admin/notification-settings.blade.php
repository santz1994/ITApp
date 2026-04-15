@extends('layouts.app')

@section('title', 'System Notification Settings')

@section('main-content')
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-bell"></i> System Notification Settings</h3>
        <p class="help-block" style="margin-top: 10px; margin-bottom: 0;">Configure global notification settings for the entire system. These settings control which notification channels are enabled.</p>
    </div>

    <form action="{{ route('notification-settings.update') }}" method="POST">
        @csrf
        
        <div class="box-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <i class="icon fa fa-check"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <i class="icon fa fa-ban"></i> {{ session('error') }}
                </div>
            @endif

            @foreach($groupedSettings as $category => $settings)
            <div class="panel panel-default">
                <div class="panel-heading" style="background-color: #f4f4f4;">
                    <h4 class="panel-title" style="margin: 0;">
                        <i class="fa fa-{{ $category == 'email' ? 'envelope' : ($category == 'whatsapp' ? 'whatsapp' : 'telegram') }}"></i>
                        {{ ucfirst($category) }} Notifications
                    </h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        @foreach($settings as $setting)
                        <div class="col-md-6" style="margin-bottom: 15px;">
                            @if(str_contains($setting->key, '_enabled') || str_starts_with($setting->key, 'email_'))
                                <!-- Toggle Switch for boolean settings -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label style="display: block; margin-bottom: 10px; font-weight: bold;">
                                        {{ $setting->description }}
                                    </label>
                                    <div class="btn-group" data-toggle="buttons">
                                        <label class="btn btn-success {{ $setting->value == '1' ? 'active' : '' }}" style="min-width: 110px;">
                                            <input type="radio" name="{{ $setting->key }}_radio" value="1" {{ $setting->value == '1' ? 'checked' : '' }} onchange="this.closest('.form-group').querySelector('input[type=hidden]').value='1'">
                                            <i class="fa fa-check"></i> Enabled
                                        </label>
                                        <label class="btn btn-danger {{ $setting->value != '1' ? 'active' : '' }}" style="min-width: 110px;">
                                            <input type="radio" name="{{ $setting->key }}_radio" value="0" {{ $setting->value != '1' ? 'checked' : '' }} onchange="this.closest('.form-group').querySelector('input[type=hidden]').value='0'">
                                            <i class="fa fa-times"></i> Disabled
                                        </label>
                                    </div>
                                    <input type="hidden" name="{{ $setting->key }}" value="{{ $setting->value }}">
                                    <p class="help-block" style="margin-top: 8px; margin-bottom: 0; font-size: 12px;">
                                        Status: <span class="label label-{{ $setting->value == '1' ? 'success' : 'danger' }}">{{ $setting->value == '1' ? 'ENABLED' : 'DISABLED' }}</span>
                                    </p>
                                </div>
                            @else
                                <!-- Text input for API URLs, tokens, etc -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label for="{{ $setting->key }}" style="font-weight: bold;">{{ $setting->description }}</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="{{ $setting->key }}"
                                           name="{{ $setting->key }}" 
                                           value="{{ $setting->value }}"
                                           placeholder="Enter {{ strtolower($setting->description) }}">
                                    <p class="help-block" style="margin-top: 5px; margin-bottom: 0; font-size: 11px;">
                                        Setting key: <code>{{ $setting->key }}</code>
                                    </p>
                                </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach

            <div class="callout callout-info">
                <h4><i class="fa fa-info-circle"></i> Important Note</h4>
                <p style="margin-bottom: 0;">
                    These are <strong>system-wide settings</strong> that affect all users globally. 
                    Individual users can configure their personal notification preferences in their <a href="{{ route('profile.edit') }}">Profile Settings</a>.
                </p>
            </div>
        </div>

        <div class="box-footer">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fa fa-save"></i> Save System Settings
            </button>
            <a href="{{ route('home') }}" class="btn btn-default btn-lg">
                <i class="fa fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
.panel {
    margin-bottom: 25px;
    border: 1px solid #d2d6de;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
}
.panel-heading {
    padding: 15px;
    border-bottom: 1px solid #d2d6de;
}
.panel-body {
    padding: 20px;
}
.btn-group label.btn {
    padding: 8px 16px;
    font-weight: normal;
}
.btn-group label.btn input[type="radio"] {
    display: none;
}
</style>
@endpush
