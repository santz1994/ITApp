@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/ui-enhancements.css') }}">
<style>
    .password-strength-meter {
        height: 5px;
        margin-top: 8px;
        background-color: #e0e0e0;
        border-radius: 3px;
        overflow: hidden;
    }
    .password-strength-bar {
        height: 100%;
        width: 0;
        transition: all 0.3s ease;
    }
    .strength-very-weak { background-color: #dc3545; width: 20%; }
    .strength-weak { background-color: #fd7e14; width: 40%; }
    .strength-fair { background-color: #ffc107; width: 60%; }
    .strength-good { background-color: #20c997; width: 80%; }
    .strength-strong { background-color: #28a745; width: 100%; }
</style>
@endsection

@section('main-content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Create New User
            <small>Add a new user to the system</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('/home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li><a href="{{ route('users.index') }}">Users</a></li>
            <li class="active">Create</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12 col-sm-8 col-md-8">
                <div class="box box-primary">
                    <form method="POST" action="{{ route('users.store') }}" role="form" id="userCreateForm">
                        @csrf
                        <div class="box-body">
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <h4><i class="fa fa-exclamation-circle"></i> Validation Errors</h4>
                                    <ul class="list-unstyled">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Basic Information Section -->
                            <fieldset class="form-fieldset">
                                <legend><i class="fa fa-user"></i> Basic Information</legend>
                                
                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                        <label for="name">Full Name <span class="text-red">*</span></label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="name" 
                                               name="name" 
                                               value="{{ old('name') }}" 
                                               placeholder="Enter full name"
                                               required
                                               autofocus>
                                        @if($errors->has('name'))
                                            <span class="help-block">{{ $errors->first('name') }}</span>
                                        @endif
                                        <small class="form-text text-muted">
                                            <i class="fa fa-info-circle"></i> Enter the user's complete full name (first and last name)
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                                        <label for="email">Email Address <span class="text-red">*</span></label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="email" 
                                               name="email" 
                                               value="{{ old('email') }}" 
                                               placeholder="user@company.com"
                                               required>
                                        @if($errors->has('email'))
                                            <span class="help-block">{{ $errors->first('email') }}</span>
                                        @endif
                                        <small class="form-text text-muted">
                                            <i class="fa fa-info-circle"></i> Must be unique. Used for login and notifications
                                        </small>
                                    </div>
                                </div>
                            </div>
                            </fieldset>

                            <!-- Security Section -->
                            <fieldset class="form-fieldset">
                                <legend><i class="fa fa-lock"></i> Security & Password</legend>
                                
                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                                        <label for="password">Password <span class="text-red">*</span></label>
                                        <div class="input-group">
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="password" 
                                                   name="password" 
                                                   placeholder="Minimum 8 characters"
                                                   required>
                                            <span class="input-group-addon" style="cursor: pointer;" onclick="togglePassword('password')">
                                                <i class="fa fa-eye" id="password-toggle-icon"></i>
                                            </span>
                                        </div>
                                        <div class="password-strength-meter">
                                            <div class="password-strength-bar" id="strength-bar"></div>
                                        </div>
                                        <small id="strength-text" class="form-text"></small>
                                        @if($errors->has('password'))
                                            <span class="help-block">{{ $errors->first('password') }}</span>
                                        @endif
                                        <small class="form-text text-muted">
                                            <i class="fa fa-info-circle"></i> Minimum 8 characters. Include uppercase, lowercase, numbers, and symbols for stronger security
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                                        <label for="password_confirmation">Confirm Password <span class="text-red">*</span></label>
                                        <div class="input-group">
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="password_confirmation" 
                                                   name="password_confirmation" 
                                                   placeholder="Re-enter password"
                                                   required>
                                            <span class="input-group-addon" style="cursor: pointer;" onclick="togglePassword('password_confirmation')">
                                                <i class="fa fa-eye" id="password_confirmation-toggle-icon"></i>
                                            </span>
                                        </div>
                                        <small id="password-match" class="form-text"></small>
                                        @if($errors->has('password_confirmation'))
                                            <span class="help-block">{{ $errors->first('password_confirmation') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            </fieldset>

                            <!-- Division & Role Section -->
                            <fieldset class="form-fieldset">
                                <legend><i class="fa fa-users"></i> Division & Role Assignment</legend>
                                
                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group {{ $errors->has('division_id') ? 'has-error' : '' }}">
                                        <label for="division_id">Division <span class="text-red">*</span></label>
                                        <select class="form-control select2" id="division_id" name="division_id" required>
                                            <option value="">-- Select Division --</option>
                                            @if(isset($divisions))
                                                @foreach($divisions as $division)
                                                    @if($division && is_object($division) && isset($division->name) && isset($division->id))
                                                        <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>
                                                            {{ $division->name }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                        @if($errors->has('division_id'))
                                            <span class="help-block">{{ $errors->first('division_id') }}</span>
                                        @endif
                                        <small class="form-text text-muted">
                                            <i class="fa fa-info-circle"></i> Organizational division or department the user belongs to
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group {{ $errors->has('role_id') ? 'has-error' : '' }}">
                                        <label for="role_id">User Role <span class="text-red">*</span></label>
                                        <select class="form-control select2" id="role_id" name="role_id" required>
                                            <option value="">-- Select Role --</option>
                                            @if(isset($roles) && count($roles) > 0)
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                        {{ $role->display_name ?? ucfirst(str_replace('-', ' ', $role->name)) }}
                                                    </option>
                                                @endforeach
                                            @else
                                                <option value="" disabled>No roles available</option>
                                            @endif
                                        </select>
                                        @if($errors->has('role_id'))
                                            <span class="help-block">{{ $errors->first('role_id') }}</span>
                                        @endif
                                        <small class="form-text text-muted">
                                            <i class="fa fa-info-circle"></i> Defines user's permissions and access level in the system
                                        </small>
                                    </div>
                                </div>
                            </div>
                            </fieldset>

                            <!-- Contact Information Section -->
                            <fieldset class="form-fieldset">
                                <legend><i class="fa fa-phone"></i> Contact Information</legend>
                                
                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">
                                        <label for="phone">Phone Number</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="phone" 
                                               name="phone" 
                                               value="{{ old('phone') }}" 
                                               placeholder="+1234567890">
                                        @if($errors->has('phone'))
                                            <span class="help-block">{{ $errors->first('phone') }}</span>
                                        @endif
                                        <small class="form-text text-muted">
                                            <i class="fa fa-info-circle"></i> Optional - User's contact phone number for urgent matters
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <label for="is_active">Account Status</label>
                                        <div class="checkbox">
                                            <label style="font-weight: normal;">
                                                <input type="checkbox" 
                                                       id="is_active" 
                                                       name="is_active" 
                                                       value="1" 
                                                       {{ old('is_active', 1) ? 'checked' : '' }}>
                                                <strong>Active User</strong> - Can login and access the system
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="fa fa-info-circle"></i> Uncheck to create inactive user account (cannot login)
                                        </small>
                                    </div>
                                </div>
                            </div>
                            </fieldset>
                        </div>

                        <div class="box-footer" style="border-top: 2px solid #3c8dbc; padding: 15px;">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fa fa-save"></i> Create User
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-default btn-lg">
                                <i class="fa fa-arrow-left"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar with Tips -->
            <div class="col-xs-12 col-sm-4 col-md-4">
                <div class="box box-solid">
                    <div class="box-header with-border bg-light-blue">
                        <h3 class="box-title"><i class="fa fa-lightbulb-o"></i> User Creation Tips</h3>
                    </div>
                    <div class="box-body">
                        <h4 class="text-bold"><i class="fa fa-info-circle text-blue"></i> Required Fields</h4>
                        <ul class="list-unstyled" style="line-height: 1.8;">
                            <li><i class="fa fa-check text-green"></i> Full Name (first and last)</li>
                            <li><i class="fa fa-check text-green"></i> Valid email address</li>
                            <li><i class="fa fa-check text-green"></i> Secure password (min 8 chars)</li>
                            <li><i class="fa fa-check text-green"></i> Division assignment</li>
                            <li><i class="fa fa-check text-green"></i> User role</li>
                        </ul>

                        <hr>

                        <h4 class="text-bold"><i class="fa fa-shield text-yellow"></i> Password Guidelines</h4>
                        <ul class="list-unstyled" style="line-height: 1.8;">
                            <li><i class="fa fa-key text-muted"></i> Minimum 8 characters</li>
                            <li><i class="fa fa-key text-muted"></i> Mix uppercase & lowercase</li>
                            <li><i class="fa fa-key text-muted"></i> Include numbers</li>
                            <li><i class="fa fa-key text-muted"></i> Add special characters (!@#$%)</li>
                        </ul>

                        <hr>

                        <h4 class="text-bold"><i class="fa fa-users text-purple"></i> Role Descriptions</h4>
                        <div class="callout callout-info" style="margin-bottom: 10px; padding: 8px;">
                            <p style="margin: 0;"><strong>Super Administrator:</strong> Full system access</p>
                        </div>
                        <div class="callout callout-warning" style="margin-bottom: 10px; padding: 8px;">
                            <p style="margin: 0;"><strong>Administrator:</strong> Manage users & assets</p>
                        </div>
                        <div class="callout callout-success" style="margin-bottom: 10px; padding: 8px;">
                            <p style="margin: 0;"><strong>User:</strong> Standard access level</p>
                        </div>
                    </div>
                </div>

                <div class="box box-solid box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-envelope"></i> Email Notification</h3>
                    </div>
                    <div class="box-body">
                        <p><i class="fa fa-info-circle"></i> An automatic email will be sent to the user with:</p>
                        <ul style="line-height: 1.8;">
                            <li>Login credentials</li>
                            <li>System access instructions</li>
                            <li>Password reset link</li>
                        </ul>
                        <div class="alert alert-warning" style="margin-top: 10px; margin-bottom: 0;">
                            <i class="fa fa-exclamation-triangle"></i> <strong>Important:</strong> Verify the email address is correct before creating the user.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function togglePassword(fieldId) {
    var field = document.getElementById(fieldId);
    var icon = document.getElementById(fieldId + '-toggle-icon');
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap',
        width: '100%'
    });

    // Password strength indicator
    $('#password').on('keyup', function() {
        var password = $(this).val();
        var strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        
        var strengthText = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        var strengthClass = ['strength-very-weak', 'strength-weak', 'strength-fair', 'strength-good', 'strength-strong'];
        var strengthColor = ['text-danger', 'text-warning', 'text-warning', 'text-info', 'text-success'];
        
        if (password.length > 0 && strength > 0) {
            $('#strength-bar').removeClass().addClass('password-strength-bar ' + strengthClass[strength-1]);
            $('#strength-text').removeClass().addClass('form-text ' + strengthColor[strength-1])
                .html('<i class="fa fa-shield"></i> Password Strength: <strong>' + strengthText[strength-1] + '</strong>');
        } else {
            $('#strength-bar').removeClass().addClass('password-strength-bar');
            $('#strength-text').html('');
        }
    });
    
    // Confirm password validation
    $('#password_confirmation').on('keyup', function() {
        var password = $('#password').val();
        var confirmPassword = $(this).val();
        
        if (confirmPassword.length > 0) {
            if (password === confirmPassword) {
                $(this).closest('.form-group').removeClass('has-error').addClass('has-success');
                $('#password-match').html('<i class="fa fa-check-circle text-success"></i> Passwords match');
            } else {
                $(this).closest('.form-group').removeClass('has-success').addClass('has-error');
                $('#password-match').html('<i class="fa fa-times-circle text-danger"></i> Passwords do not match');
            }
        } else {
            $(this).closest('.form-group').removeClass('has-error has-success');
            $('#password-match').html('');
        }
    });

    // Form submission with loading state
    $('#userCreateForm').on('submit', function() {
        $('#submitBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Creating User...');
    });
});
</script>
@endsection
