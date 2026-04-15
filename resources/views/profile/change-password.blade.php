@extends('layouts.app')

@section('title', 'Change Password')

@section('main-content')
<div class="row">
    {{-- Profile Sidebar --}}
    <div class="col-md-3">
        <div class="box box-primary">
            <div class="box-body box-profile">
                <img class="profile-user-img img-responsive img-circle" 
                     src="{{ auth()->user()->profile_picture ? asset('storage/' . auth()->user()->profile_picture) : asset('img/default-avatar.png') }}" 
                     alt="User profile picture">
                <h3 class="profile-username text-center">{{ auth()->user()->name }}</h3>
                <p class="text-muted text-center">{{ auth()->user()->email }}</p>

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
        <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-lock"></i> Change Your Password</h3>
                </div>
                
                <form method="POST" action="{{ route('profile.update-password') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="box-body">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> 
                            <strong>Password Requirements:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Minimum 8 characters long</li>
                                <li>Must match the confirmation field</li>
                            </ul>
                        </div>

                        {{-- Current Password --}}
                        <div class="form-group @error('current_password') has-error @enderror">
                            <label for="current_password">Current Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                <input type="password" 
                                       class="form-control" 
                                       id="current_password" 
                                       name="current_password" 
                                       placeholder="Enter your current password"
                                       required>
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" onclick="togglePassword('current_password')">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </span>
                            </div>
                            @error('current_password')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <hr>

                        {{-- New Password --}}
                        <div class="form-group @error('password') has-error @enderror">
                            <label for="password">New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Enter new password (min. 8 characters)"
                                       required
                                       minlength="8">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" onclick="togglePassword('password')">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </span>
                            </div>
                            @error('password')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                            <div id="password-strength" class="mt-2"></div>
                        </div>

                        {{-- Confirm Password --}}
                        <div class="form-group @error('password_confirmation') has-error @enderror">
                            <label for="password_confirmation">Confirm New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       placeholder="Re-enter new password"
                                       required
                                       minlength="8">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" onclick="togglePassword('password_confirmation')">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </span>
                            </div>
                            @error('password_confirmation')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                            <div id="password-match" class="mt-2"></div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-warning">
                            <i class="fa fa-save"></i> Update Password
                        </button>
                        <a href="{{ route('profile.edit') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back to Profile
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// Toggle password visibility
function togglePassword(fieldId) {
    var field = document.getElementById(fieldId);
    var icon = event.currentTarget.querySelector('i');
    
    if (field.type === "password") {
        field.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
    var password = this.value;
    var strengthDiv = document.getElementById('password-strength');
    var strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z\d]/.test(password)) strength++;
    
    var strengthText = '';
    var strengthClass = '';
    
    if (password.length === 0) {
        strengthDiv.innerHTML = '';
        return;
    }
    
    if (strength <= 2) {
        strengthText = 'Weak';
        strengthClass = 'text-danger';
    } else if (strength <= 3) {
        strengthText = 'Medium';
        strengthClass = 'text-warning';
    } else {
        strengthText = 'Strong';
        strengthClass = 'text-success';
    }
    
    strengthDiv.innerHTML = '<small class="' + strengthClass + '"><i class="fa fa-info-circle"></i> Password Strength: <strong>' + strengthText + '</strong></small>';
});

// Password match indicator
document.getElementById('password_confirmation').addEventListener('input', function() {
    var password = document.getElementById('password').value;
    var confirmation = this.value;
    var matchDiv = document.getElementById('password-match');
    
    if (confirmation.length === 0) {
        matchDiv.innerHTML = '';
        return;
    }
    
    if (password === confirmation) {
        matchDiv.innerHTML = '<small class="text-success"><i class="fa fa-check-circle"></i> Passwords match</small>';
    } else {
        matchDiv.innerHTML = '<small class="text-danger"><i class="fa fa-times-circle"></i> Passwords do not match</small>';
    }
});
</script>
@endpush

@push('styles')
<style>
    .mt-2 {
        margin-top: 8px;
    }
    .mb-0 {
        margin-bottom: 0;
    }
    .input-group-btn button {
        height: 34px;
    }
</style>
@endpush
