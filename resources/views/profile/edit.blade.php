@extends('layouts.app')

@section('title', 'My Profile')

@section('main-content')
<div class="row">
    {{-- Profile Sidebar --}}
    <div class="col-md-3">
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

    {{-- Profile Information Card --}}
    <div class="col-md-9">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-user"></i> Edit Profile Information</h3>
            </div>
            
            <form class="form-horizontal" method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <div class="box-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="fa fa-check"></i> {{ session('success') }}
                        </div>
                    @endif

                    <div class="form-group @error('name') has-error @enderror">
                        <label for="name" class="col-sm-3 control-label">Full Name <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group @error('email') has-error @enderror">
                        <label for="email" class="col-sm-3 control-label">Email <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group @error('phone') has-error @enderror">
                        <label for="phone" class="col-sm-3 control-label">Phone</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="phone" name="phone" 
                                   value="{{ old('phone', $user->phone) }}" placeholder="e.g., +62 812 3456 7890">
                            @error('phone')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group @error('location_id') has-error @enderror">
                        <label for="location_id" class="col-sm-3 control-label">Location</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="location_id" name="location_id">
                                <option value="">-- Select Location --</option>
                                @foreach(\App\Location::orderBy('location_name')->get() as $location)
                                    <option value="{{ $location->id }}" 
                                        {{ old('location_id', $user->location_id) == $location->id ? 'selected' : '' }}>
                                        {{ $location->location_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('location_id')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group @error('division_id') has-error @enderror">
                        <label for="division_id" class="col-sm-3 control-label">Division</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="division_id" name="division_id">
                                <option value="">-- Select Division --</option>
                                @foreach(\App\Division::orderBy('name')->get() as $division)
                                    <option value="{{ $division->id }}" 
                                        {{ old('division_id', $user->division_id) == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('division_id')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa fa-save"></i> Update Profile
                    </button>
                    <a href="{{ url('/home') }}" class="btn btn-default btn-lg">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .profile-user-img {
        margin: 0 auto;
        width: 100px;
        padding: 3px;
        border: 3px solid #d2d6de;
    }
    .profile-username {
        font-size: 21px;
        margin-top: 10px;
    }
    .nav-tabs-custom {
        margin-bottom: 20px;
        background: #fff;
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        border-radius: 3px;
    }
</style>
@endpush
