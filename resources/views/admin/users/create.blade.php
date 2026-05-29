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
            <span data-i18n="users.create.form.title">Create New User</span>
            <small data-i18n="users.create.form.subtitle">Add a new user to the system</small>
        </h1>
        <div class="pull-right" style="margin-top: -34px;">
            <div class="btn-group btn-group-xs" role="group" aria-label="User Create Language Toggle">
                <button type="button" class="btn btn-default" id="userCreateLanguageEnglish" data-lang="en">EN</button>
                <button type="button" class="btn btn-default" id="userCreateLanguageIndonesian" data-lang="id">ID</button>
            </div>
        </div>
        <div class="clearfix"></div>
        <ol class="breadcrumb">
            <li><a href="{{ url('/home') }}"><i class="fa fa-dashboard"></i> <span data-i18n="users.create.breadcrumb.home">Home</span></a></li>
            <li><a href="{{ url('/home') }}" data-i18n="users.create.breadcrumb.admin">Admin</a></li>
            <li><a href="{{ route('users.index') }}" data-i18n="users.create.breadcrumb.users">Users</a></li>
            <li class="active" data-i18n="users.create.breadcrumb.create">Create</li>
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
                                    <h4><i class="fa fa-exclamation-circle"></i> <span data-i18n="users.create.alert.validation">Validation Errors</span></h4>
                                    <ul class="list-unstyled">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Basic Information Section -->
                            <fieldset class="form-fieldset">
                                <legend><i class="fa fa-user"></i> <span data-i18n="users.create.section.basic">Basic Information</span></legend>
                                
                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                        <label for="name"><span data-i18n="users.create.label.name">Full Name</span> <span class="text-red">*</span></label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="name" 
                                               name="name" 
                                               value="{{ old('name') }}" 
                                               placeholder="Enter full name"
                                               data-i18n-placeholder="users.create.placeholder.name"
                                               required
                                               autofocus>
                                        @if($errors->has('name'))
                                            <span class="help-block">{{ $errors->first('name') }}</span>
                                        @endif
                                        <small class="form-text text-muted">
                                            <i class="fa fa-info-circle"></i> <span data-i18n="users.create.help.name">Enter the user's complete full name (first and last name)</span>
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                                        <label for="email"><span data-i18n="users.create.label.email">Email Address</span> <span class="text-red">*</span></label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="email" 
                                               name="email" 
                                               value="{{ old('email') }}" 
                                               placeholder="user@company.com"
                                               data-i18n-placeholder="users.create.placeholder.email"
                                               required>
                                        @if($errors->has('email'))
                                            <span class="help-block">{{ $errors->first('email') }}</span>
                                        @endif
                                        <small class="form-text text-muted">
                                            <i class="fa fa-info-circle"></i> <span data-i18n="users.create.help.email">Must be unique. Used for login and notifications</span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            </fieldset>

                            <!-- Security Section -->
                            <fieldset class="form-fieldset">
                                <legend><i class="fa fa-lock"></i> <span data-i18n="users.create.section.security">Security & Password</span></legend>
                                
                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                                        <label for="password"><span data-i18n="users.create.label.password">Password</span> <span class="text-red">*</span></label>
                                        <div class="input-group">
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="password" 
                                                   name="password" 
                                                   placeholder="Minimum 8 characters"
                                                   data-i18n-placeholder="users.create.placeholder.password"
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
                                            <i class="fa fa-info-circle"></i> <span data-i18n="users.create.help.password">Minimum 8 characters. Include uppercase, lowercase, numbers, and symbols for stronger security</span>
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                                        <label for="password_confirmation"><span data-i18n="users.create.label.password_confirmation">Confirm Password</span> <span class="text-red">*</span></label>
                                        <div class="input-group">
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="password_confirmation" 
                                                   name="password_confirmation" 
                                                   placeholder="Re-enter password"
                                                   data-i18n-placeholder="users.create.placeholder.password_confirmation"
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
                                <legend><i class="fa fa-users"></i> <span data-i18n="users.create.section.assignment">Division & Role Assignment</span></legend>
                                
                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group {{ $errors->has('division_id') ? 'has-error' : '' }}">
                                        <label for="division_id"><span data-i18n="users.create.label.division">Division</span> <span class="text-red">*</span></label>
                                        <select class="form-control select2" id="division_id" name="division_id" required>
                                            <option value="" data-i18n="users.create.option.select_division">-- Select Division --</option>
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
                                            <i class="fa fa-info-circle"></i> <span data-i18n="users.create.help.division">Organizational division or department the user belongs to</span>
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group {{ $errors->has('role_id') ? 'has-error' : '' }}">
                                        <label for="role_id"><span data-i18n="users.create.label.role">User Role</span> <span class="text-red">*</span></label>
                                        <select class="form-control select2" id="role_id" name="role_id" required>
                                            <option value="" data-i18n="users.create.option.select_role">-- Select Role --</option>
                                            @if(isset($roles) && count($roles) > 0)
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                        {{ $role->display_name ?? ucfirst(str_replace('-', ' ', $role->name)) }}
                                                    </option>
                                                @endforeach
                                            @else
                                                    <option value="" disabled data-i18n="users.create.option.no_roles">No roles available</option>
                                            @endif
                                        </select>
                                        @if($errors->has('role_id'))
                                            <span class="help-block">{{ $errors->first('role_id') }}</span>
                                        @endif
                                        <small class="form-text text-muted">
                                            <i class="fa fa-info-circle"></i> <span data-i18n="users.create.help.role">Defines user's permissions and access level in the system</span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            </fieldset>

                            <!-- Contact Information Section -->
                            <fieldset class="form-fieldset">
                                <legend><i class="fa fa-phone"></i> <span data-i18n="users.create.section.contact">Contact Information</span></legend>
                                
                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">
                                        <label for="phone" data-i18n="users.create.label.phone">Phone Number</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="phone" 
                                               name="phone" 
                                               value="{{ old('phone') }}" 
                                               placeholder="+1234567890"
                                               data-i18n-placeholder="users.create.placeholder.phone">
                                        @if($errors->has('phone'))
                                            <span class="help-block">{{ $errors->first('phone') }}</span>
                                        @endif
                                        <small class="form-text text-muted">
                                            <i class="fa fa-info-circle"></i> <span data-i18n="users.create.help.phone">Optional - User's contact phone number for urgent matters</span>
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <label for="is_active" data-i18n="users.create.label.account_status">Account Status</label>
                                        <div class="checkbox">
                                            <label style="font-weight: normal;">
                                                <input type="checkbox" 
                                                       id="is_active" 
                                                       name="is_active" 
                                                       value="1" 
                                                       {{ old('is_active', 1) ? 'checked' : '' }}>
                                                <strong data-i18n="users.create.label.active_user">Active User</strong> - <span data-i18n="users.create.help.active_user">Can login and access the system</span>
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="fa fa-info-circle"></i> <span data-i18n="users.create.help.account_status">Uncheck to create inactive user account (cannot login)</span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            </fieldset>
                        </div>

                        <div class="box-footer" style="border-top: 2px solid #3c8dbc; padding: 15px;">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fa fa-save"></i> <span data-i18n="users.create.action.submit">Create User</span>
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-default btn-lg">
                                <i class="fa fa-arrow-left"></i> <span data-i18n="users.create.action.cancel">Cancel</span>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar with Tips -->
            <div class="col-xs-12 col-sm-4 col-md-4">
                <div class="box box-solid">
                    <div class="box-header with-border bg-light-blue">
                        <h3 class="box-title"><i class="fa fa-lightbulb-o"></i> <span data-i18n="users.create.tips.title">User Creation Tips</span></h3>
                    </div>
                    <div class="box-body">
                        <h4 class="text-bold"><i class="fa fa-info-circle text-blue"></i> <span data-i18n="users.create.tips.required_fields">Required Fields</span></h4>
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
                        <h3 class="box-title"><i class="fa fa-envelope"></i> <span data-i18n="users.create.email_notification.title">Email Notification</span></h3>
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

(function() {
    var translations = {
        en: {
            'users.create.form.title': 'Create New User',
            'users.create.form.subtitle': 'Add a new user to the system',
            'users.create.section.basic': 'Basic Information',
            'users.create.action.submit': 'Create User',
            'users.create.action.cancel': 'Cancel',
            'users.create.placeholder.name': 'Enter full name',
            'users.create.placeholder.email': 'user@company.com',
            'users.create.placeholder.password': 'Minimum 8 characters',
            'users.create.placeholder.password_confirmation': 'Re-enter password',
            'users.create.placeholder.phone': '+1234567890',
            'users.create.runtime.password_strength_prefix': 'Password Strength:',
            'users.create.runtime.password_strength.very_weak': 'Very Weak',
            'users.create.runtime.password_strength.weak': 'Weak',
            'users.create.runtime.password_strength.fair': 'Fair',
            'users.create.runtime.password_strength.good': 'Good',
            'users.create.runtime.password_strength.strong': 'Strong',
            'users.create.runtime.password_match': 'Passwords match',
            'users.create.runtime.password_mismatch': 'Passwords do not match',
            'users.create.runtime.loading': 'Creating User...'
        },
        id: {
            'users.create.form.title': 'Buat Pengguna Baru',
            'users.create.form.subtitle': 'Tambahkan pengguna baru ke sistem',
            'users.create.section.basic': 'Informasi Dasar',
            'users.create.action.submit': 'Buat Pengguna',
            'users.create.action.cancel': 'Batal',
            'users.create.placeholder.name': 'Masukkan nama lengkap',
            'users.create.placeholder.email': 'pengguna@perusahaan.com',
            'users.create.placeholder.password': 'Minimal 8 karakter',
            'users.create.placeholder.password_confirmation': 'Masukkan ulang kata sandi',
            'users.create.placeholder.phone': '+628123456789',
            'users.create.runtime.password_strength_prefix': 'Kekuatan Kata Sandi:',
            'users.create.runtime.password_strength.very_weak': 'Sangat Lemah',
            'users.create.runtime.password_strength.weak': 'Lemah',
            'users.create.runtime.password_strength.fair': 'Cukup',
            'users.create.runtime.password_strength.good': 'Baik',
            'users.create.runtime.password_strength.strong': 'Sangat Baik',
            'users.create.runtime.password_match': 'Kata sandi cocok',
            'users.create.runtime.password_mismatch': 'Kata sandi tidak cocok',
            'users.create.runtime.loading': 'Sedang membuat pengguna...'
        }
    };

    var currentLanguage = 'en';
    var userId = '{{ (int) auth()->id() }}';
    var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
    var englishButton = document.getElementById('userCreateLanguageEnglish');
    var indonesianButton = document.getElementById('userCreateLanguageIndonesian');

    function getLanguage() {
        try {
            var raw = window.localStorage.getItem(languageStorageKey);
            if (!raw) {
                return 'en';
            }

            var parsed = JSON.parse(raw);
            return parsed && parsed.language === 'id' ? 'id' : 'en';
        } catch (error) {
            return 'en';
        }
    }

    function saveLanguage(language) {
        try {
            var raw = window.localStorage.getItem(languageStorageKey);
            var parsed = raw ? JSON.parse(raw) : {};
            parsed.language = language === 'id' ? 'id' : 'en';
            window.localStorage.setItem(languageStorageKey, JSON.stringify(parsed));
        } catch (error) {
            // Keep silent if localStorage is unavailable.
        }
    }

    function getLabel(key, fallback) {
        var dictionary = translations[currentLanguage] || translations.en;
        return dictionary[key] || fallback || key;
    }

    function applyLanguage(language) {
        currentLanguage = language === 'id' ? 'id' : 'en';
        var dictionary = translations[currentLanguage] || translations.en;

        Array.prototype.forEach.call(document.querySelectorAll('[data-i18n]'), function(node) {
            var key = node.getAttribute('data-i18n');
            if (dictionary[key]) {
                node.textContent = dictionary[key];
            }
        });

        Array.prototype.forEach.call(document.querySelectorAll('[data-i18n-placeholder]'), function(node) {
            var key = node.getAttribute('data-i18n-placeholder');
            if (dictionary[key]) {
                node.setAttribute('placeholder', dictionary[key]);
            }
        });

        if (englishButton && indonesianButton) {
            englishButton.classList.toggle('active', currentLanguage === 'en');
            indonesianButton.classList.toggle('active', currentLanguage === 'id');
        }
    }

    window.userCreateLabel = getLabel;

    if (englishButton && indonesianButton) {
        englishButton.addEventListener('click', function() {
            saveLanguage('en');
            applyLanguage('en');
        });

        indonesianButton.addEventListener('click', function() {
            saveLanguage('id');
            applyLanguage('id');
        });
    }

    applyLanguage(getLanguage());
})();

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
        
        var strengthText = [
            window.userCreateLabel('users.create.runtime.password_strength.very_weak', 'Very Weak'),
            window.userCreateLabel('users.create.runtime.password_strength.weak', 'Weak'),
            window.userCreateLabel('users.create.runtime.password_strength.fair', 'Fair'),
            window.userCreateLabel('users.create.runtime.password_strength.good', 'Good'),
            window.userCreateLabel('users.create.runtime.password_strength.strong', 'Strong')
        ];
        var strengthClass = ['strength-very-weak', 'strength-weak', 'strength-fair', 'strength-good', 'strength-strong'];
        var strengthColor = ['text-danger', 'text-warning', 'text-warning', 'text-info', 'text-success'];
        
        if (password.length > 0 && strength > 0) {
            $('#strength-bar').removeClass().addClass('password-strength-bar ' + strengthClass[strength-1]);
            $('#strength-text').removeClass().addClass('form-text ' + strengthColor[strength-1])
                .html('<i class="fa fa-shield"></i> ' + window.userCreateLabel('users.create.runtime.password_strength_prefix', 'Password Strength:') + ' <strong>' + strengthText[strength-1] + '</strong>');
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
                $('#password-match').html('<i class="fa fa-check-circle text-success"></i> ' + window.userCreateLabel('users.create.runtime.password_match', 'Passwords match'));
            } else {
                $(this).closest('.form-group').removeClass('has-success').addClass('has-error');
                $('#password-match').html('<i class="fa fa-times-circle text-danger"></i> ' + window.userCreateLabel('users.create.runtime.password_mismatch', 'Passwords do not match'));
            }
        } else {
            $(this).closest('.form-group').removeClass('has-error has-success');
            $('#password-match').html('');
        }
    });

    // Form submission with loading state
    $('#userCreateForm').on('submit', function() {
        $('#submitBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ' + window.userCreateLabel('users.create.runtime.loading', 'Creating User...'));
    });
});
</script>
@endsection
