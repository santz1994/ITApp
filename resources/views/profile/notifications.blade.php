@extends('layouts.app')

@section('title', 'Notification Preferences')

@section('main-content')
<div class="row">
    <div class="col-md-3">
        <!-- Profile Sidebar -->
        <div class="box box-primary">
            <div class="box-body box-profile">
                <img class="profile-user-img img-responsive img-circle" 
                     src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : asset('img/default-avatar.png') }}" 
                     alt="User profile picture">
                <h3 class="profile-username text-center">{{ $user->name }}</h3>
                <p class="text-muted text-center">{{ $user->email }}</p>

                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item">
                        <a href="{{ route('profile.edit') }}" class="{{ Request::is('profile') ? 'text-bold' : '' }}">
                            <i class="fa fa-user"></i> Edit Profile
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="{{ route('profile.edit-password') }}" class="{{ Request::is('profile/change-password') ? 'text-bold' : '' }}">
                            <i class="fa fa-lock"></i> Change Password
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="{{ route('profile.edit-picture') }}" class="{{ Request::is('profile/change-picture') ? 'text-bold' : '' }}">
                            <i class="fa fa-camera"></i> Change Picture
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="{{ route('profile.edit-notifications') }}" class="{{ Request::is('profile/notifications') ? 'text-bold' : '' }}">
                            <i class="fa fa-bell"></i> Notifications
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bell"></i> Notification Preferences</h3>
                <p class="help-block" style="margin-top: 10px; margin-bottom: 0;">
                    Choose which notifications you want to receive via email.
                </p>
            </div>

            <form action="{{ route('profile.update-notifications') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="box-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="fa fa-check"></i> {{ session('success') }}
                        </div>
                    @endif

                    <!-- Master Email Toggle -->
                    <div class="panel panel-default">
                        <div class="panel-heading" style="background-color: #f4f4f4;">
                            <h4 class="panel-title" style="margin: 0;">
                                <i class="fa fa-envelope"></i> Email Notifications
                            </h4>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 15px;">
                                    <strong style="font-size: 15px;">Master Email Notification</strong>
                                </label>
                                <div class="btn-group" data-toggle="buttons">
                                    <label class="btn btn-success {{ $user->notify_email ? 'active' : '' }}" style="min-width: 110px;">
                                        <input type="radio" name="notify_email" value="1" {{ $user->notify_email ? 'checked' : '' }}>
                                        <i class="fa fa-check"></i> Enabled
                                    </label>
                                    <label class="btn btn-danger {{ !$user->notify_email ? 'active' : '' }}" style="min-width: 110px;">
                                        <input type="radio" name="notify_email" value="0" {{ !$user->notify_email ? 'checked' : '' }}>
                                        <i class="fa fa-times"></i> Disabled
                                    </label>
                                </div>
                                <p class="help-block" style="margin-top: 10px;">
                                    Turn off to disable all email notifications. Status: 
                                    <span class="label label-{{ $user->notify_email ? 'success' : 'danger' }}">
                                        {{ $user->notify_email ? 'ENABLED' : 'DISABLED' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Ticket Notifications -->
                    <div class="panel panel-default">
                        <div class="panel-heading" style="background-color: #f4f4f4;">
                            <h4 class="panel-title" style="margin: 0;">
                                <i class="fa fa-ticket"></i> Ticket Notifications
                            </h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="notify_ticket_created" {{ $user->notify_ticket_created ? 'checked' : '' }}>
                                            <strong>Ticket Created</strong>
                                        </label>
                                        <p class="help-block" style="margin-left: 20px;">When a new ticket is created</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="notify_ticket_assigned" {{ $user->notify_ticket_assigned ? 'checked' : '' }}>
                                            <strong>Ticket Assigned</strong>
                                        </label>
                                        <p class="help-block" style="margin-left: 20px;">When a ticket is assigned to you</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="notify_ticket_updated" {{ $user->notify_ticket_updated ? 'checked' : '' }}>
                                            <strong>Ticket Updated</strong>
                                        </label>
                                        <p class="help-block" style="margin-left: 20px;">When ticket status or priority changes</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Meeting Room Notifications -->
                    <div class="panel panel-default">
                        <div class="panel-heading" style="background-color: #f4f4f4;">
                            <h4 class="panel-title" style="margin: 0;">
                                <i class="fa fa-calendar"></i> Meeting Room Notifications
                            </h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="notify_meeting_approved" {{ $user->notify_meeting_approved ? 'checked' : '' }}>
                                            <strong>Booking Approved</strong>
                                        </label>
                                        <p class="help-block" style="margin-left: 20px;">When your meeting room booking is approved</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="notify_meeting_rejected" {{ $user->notify_meeting_rejected ? 'checked' : '' }}>
                                            <strong>Booking Rejected</strong>
                                        </label>
                                        <p class="help-block" style="margin-left: 20px;">When your meeting room booking is rejected</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info">
                        <h4><i class="fa fa-info-circle"></i> Note</h4>
                        <p style="margin-bottom: 0;">
                            These preferences only apply if the system-wide notifications are enabled by the administrator.
                            Contact your system administrator if you're not receiving notifications even with these settings enabled.
                        </p>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa fa-save"></i> Save Preferences
                    </button>
                    <a href="{{ route('profile.edit') }}" class="btn btn-default btn-lg">
                        <i class="fa fa-arrow-left"></i> Back to Profile
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.panel {
    margin-bottom: 20px;
    border: 1px solid #d2d6de;
}
.panel-heading {
    padding: 15px;
    border-bottom: 1px solid #d2d6de;
}
.panel-body {
    padding: 20px;
}
.checkbox {
    margin-bottom: 15px;
}
.checkbox label {
    font-weight: normal;
}
.btn-group label.btn input[type="radio"] {
    display: none;
}
</style>
@endpush
