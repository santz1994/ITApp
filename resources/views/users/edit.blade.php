@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css" />
@endsection

@section('main-content')

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Edit User: ' . $user->name,
    'subtitle' => 'Modify user details and permissions',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Users', 'url' => route('users.index')],
        ['label' => 'Edit']
    ],
    'actions' => '
        <div class="btn-group" role="group">
            <a href="'.route('users.show', $user->id).'" class="btn btn-info">
                <i class="fa fa-eye"></i> <span class="hidden-xs">View</span>
            </a>
            <a href="'.route('users.index').'" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> <span class="hidden-xs">Back</span>
            </a>
        </div>
    '
])

<div class="container-fluid">
    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <i class="icon fa fa-check"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <i class="icon fa fa-ban"></i> {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-warning"></i> Validation Errors!</h4>
        <ul style="margin-bottom: 0;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- User Metadata --}}
    <div class="alert alert-info" style="background-color: #fff8dc; border-color: #f0ad4e;">
        <i class="fa fa-info-circle"></i> <strong>User Information:</strong>
        Created: <strong>{{ $user->created_at->format('d M Y H:i') }}</strong> | 
        Last Updated: <strong>{{ $user->updated_at->format('d M Y H:i') }}</strong>
        @if($user->last_login_at)
        | Last Login: <strong>{{ $user->last_login_at->format('d M Y H:i') }}</strong>
        @endif
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-user-edit"></i> Edit User Information
                    </h3>
                </div>
                <form method="POST" action="{{ route('users.update', $user) }}" id="user-edit-form">
                    @csrf
                    @method('PUT')
                    <div class="box-body">
                        
                        {{-- Section 1: Basic Information --}}
                        <fieldset>
                            <legend>
                                <span class="form-section-icon"><i class="fa fa-info-circle"></i></span>
                                Basic Information
                            </legend>

                            <div class="form-group">
                                <label for="name">
                                    <i class="fa fa-user text-primary"></i> Full Name <span class="text-red">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $user->name) }}"
                                       placeholder="e.g., John Doe" 
                                       required>
                                <small class="help-text">Enter the user's complete name as it should appear in the system</small>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="email">
                                    <i class="fa fa-envelope text-primary"></i> Email Address <span class="text-red">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $user->email) }}"
                                       placeholder="e.g., john.doe@company.com" 
                                       required>
                                <small class="help-text">This will be used for login and system notifications</small>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="employee_id">
                                    <i class="fa fa-id-card text-primary"></i> Employee ID
                                </label>
                                <input type="text" 
                                       class="form-control @error('employee_id') is-invalid @enderror" 
                                       id="employee_id" 
                                       name="employee_id" 
                                       value="{{ old('employee_id', $user->employee_id ?? '') }}"
                                       placeholder="e.g., EMP-001">
                                <small class="help-text">Optional unique employee identifier for HR integration</small>
                                @error('employee_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </fieldset>

                        {{-- Section 2: Access Control --}}
                        <fieldset>
                            <legend>
                                <span class="form-section-icon"><i class="fa fa-shield-alt"></i></span>
                                Access Control & Security
                            </legend>

                            <div class="form-group">
                                <label for="role_id">
                                    <i class="fa fa-user-tag text-warning"></i> User Role <span class="text-red">*</span>
                                </label>
                                <select class="form-control @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                                    <option value="">Select a role...</option>
                                    @php $curRoleId = old('role_id', $user->roles->first()?->id ?? null); @endphp
                                    @if(isset($roles) && $roles->count() > 0)
                                        @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ $curRoleId == $role->id ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('-', ' ', $role->name)) }}
                                        </option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>No roles available</option>
                                    @endif
                                </select>
                                <small class="help-text">Changes take effect immediately (user may need to re-login)</small>
                                @error('role_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active ?? 1) ? 'checked' : '' }}> 
                                    <i class="fa fa-check-circle text-success"></i> Active Account
                                </label>
                                <small class="help-text d-block">Uncheck to deactivate the account (user won't be able to log in)</small>
                            </div>

                            <div class="form-group" style="border-top: 1px dashed #ddd; padding-top: 15px; margin-top: 15px;">
                                <label>
                                    <input type="checkbox" id="change_password" name="change_password" value="1"> 
                                    <i class="fa fa-key text-warning"></i> <strong>Change Password</strong>
                                </label>
                                <small class="help-text">Check this if you want to set a new password for this user</small>
                            </div>

                            <div id="password_fields" style="display: none; background-color: #fffacd; padding: 15px; border-radius: 5px; border: 1px solid #f0ad4e;">
                                <div class="form-group">
                                    <label for="password">
                                        <i class="fa fa-lock text-warning"></i> New Password <span class="text-red">*</span>
                                    </label>
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password"
                                           placeholder="Minimum 8 characters"
                                           autocomplete="new-password">
                                    <small class="help-text">Must be at least 8 characters long</small>
                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="password_confirmation">
                                        <i class="fa fa-lock text-warning"></i> Confirm New Password <span class="text-red">*</span>
                                    </label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation"
                                           placeholder="Re-enter password"
                                           autocomplete="new-password">
                                    <small class="help-text">Must match the password above. User will be required to re-authenticate.</small>
                                </div>
                            </div>
                        </fieldset>

                        {{-- Section 3: Organization --}}
                        <fieldset>
                            <legend>
                                <span class="form-section-icon"><i class="fa fa-building"></i></span>
                                Organization & Department
                            </legend>

                            <div class="form-group">
                                <label for="division_id">
                                    <i class="fa fa-sitemap text-info"></i> Division <span class="text-red">*</span>
                                </label>
                                <select name="division_id" id="division_id" class="form-control @error('division_id') is-invalid @enderror" required>
                                    <option value="">Select Division...</option>
                                    @php
                                        $divs = $divisions ?? (\App\Division::orderBy('name')->get() ?? collect([]));
                                        $currentDivision = old('division_id', $user->division_id ?? null);
                                    @endphp
                                    @foreach($divs as $div)
                                        <option value="{{ $div->id }}" {{ $currentDivision == $div->id ? 'selected' : '' }}>{{ $div->name }}</option>
                                    @endforeach
                                </select>
                                <small class="help-text">Department or division this user belongs to</small>
                                @error('division_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="location_id">
                                    <i class="fa fa-map-marker-alt text-info"></i> Primary Location
                                </label>
                                <select name="location_id" id="location_id" class="form-control @error('location_id') is-invalid @enderror">
                                    <option value="">Select Location...</option>
                                    @php
                                        $locations = \App\Location::orderBy('name')->get() ?? collect([]);
                                        $currentLocation = old('location_id', $user->location_id ?? null);
                                    @endphp
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ $currentLocation == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="help-text">Office or branch where this user is primarily based</small>
                                @error('location_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="position">
                                    <i class="fa fa-briefcase text-info"></i> Job Position
                                </label>
                                <input type="text" 
                                       class="form-control @error('position') is-invalid @enderror" 
                                       id="position" 
                                       name="position" 
                                       value="{{ old('position', $user->position ?? '') }}"
                                       placeholder="e.g., IT Manager, System Administrator">
                                <small class="help-text">User's job title or position in the organization</small>
                                @error('position')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </fieldset>

                        {{-- Section 4: Contact Information --}}
                        <fieldset>
                            <legend>
                                <span class="form-section-icon"><i class="fa fa-phone"></i></span>
                                Contact Information
                            </legend>

                            <div class="form-group">
                                <label for="phone">
                                    <i class="fa fa-mobile-alt text-success"></i> Mobile Phone
                                </label>
                                <input type="text" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone', $user->phone ?? '') }}"
                                       placeholder="e.g., +62 812-3456-7890">
                                <small class="help-text">Primary mobile number for SMS notifications</small>
                                @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="mobile">
                                    <i class="fa fa-phone text-success"></i> Office Phone
                                </label>
                                <input type="text" 
                                       class="form-control @error('mobile') is-invalid @enderror" 
                                       id="mobile" 
                                       name="mobile" 
                                       value="{{ old('mobile', $user->mobile ?? '') }}"
                                       placeholder="e.g., (021) 1234-5678">
                                <small class="help-text">Direct office line or desk phone</small>
                                @error('mobile')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="extension">
                                    <i class="fa fa-hashtag text-success"></i> Extension
                                </label>
                                <input type="text" 
                                       class="form-control @error('extension') is-invalid @enderror" 
                                       id="extension" 
                                       name="extension" 
                                       value="{{ old('extension', $user->extension ?? '') }}"
                                       placeholder="e.g., 101">
                                <small class="help-text">Internal phone extension number</small>
                                @error('extension')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </fieldset>

                    </div>
                    
                    <div class="box-footer" style="border-top: 2px solid #e3e3e3; padding-top: 20px; margin-top: 30px;">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-save"></i> <b>Update User</b>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-md-4">
            {{-- User Statistics --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-chart-bar"></i> User Statistics
                    </h3>
                </div>
                <div class="box-body">
                    <table class="table table-condensed">
                        <tr>
                            <td><strong>User ID:</strong></td>
                            <td>{{ $user->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Last Updated:</strong></td>
                            <td>{{ $user->updated_at->format('M d, Y') }}</td>
                        </tr>
                        @if($user->last_login_at)
                        <tr>
                            <td><strong>Last Login:</strong></td>
                            <td>{{ $user->last_login_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td><strong>Current Role:</strong></td>
                            <td>
                                @php
                                    $currentRole = $user->roles->first()?->name ?? 'none';
                                @endphp
                                <span class="label label-{{ $currentRole === 'super-admin' ? 'danger' : ($currentRole === 'admin' ? 'warning' : ($currentRole === 'management' ? 'info' : 'default')) }}">
                                    {{ ucfirst(str_replace('-', ' ', $currentRole)) }}
                                </span>
                            </td>
                        </tr>
                        {{-- Tickets metrics removed (legacy) --}}
                        {{-- Assets metrics removed (legacy) --}}
                    </table>
                </div>
            </div>

            {{-- Edit Tips --}}
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-lightbulb"></i> Edit Tips
                    </h3>
                </div>
                <div class="box-body">
                    <p><strong>Important Notes:</strong></p>
                    <ul style="font-size: 13px;">
                        <li><i class="fa fa-user-tag text-warning"></i> <strong>Role Changes:</strong> Take effect immediately, user may need to re-login</li>
                        <li><i class="fa fa-key text-danger"></i> <strong>Password Changes:</strong> Will force user re-authentication</li>
                        <li><i class="fa fa-ban text-danger"></i> <strong>Deactivating:</strong> User won't be able to log in</li>
                        <li><i class="fa fa-envelope text-info"></i> <strong>Email Changes:</strong> Will affect login credentials</li>
                    </ul>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-bolt"></i> Quick Actions
                    </h3>
                </div>
                <div class="box-body">
                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-info btn-block">
                        <i class="fa fa-eye"></i> View Full Profile
                    </a>
                    <a href="{{ route('users.index') }}" class="btn btn-default btn-block">
                        <i class="fa fa-list"></i> Back to Users List
                    </a>
                    {{-- Ticket quick actions removed (legacy) --}}
                    {{-- View Assigned Assets removed (legacy) --}}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Loading Overlay --}}
@include('components.loading-overlay')

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Form loading state
    $('#user-edit-form').on('submit', function() {
        showLoading('Updating user...');
    });

    // Password change toggle
    $('#change_password').on('change', function() {
        var passwordFields = $('#password_fields');
        var passwordInput = $('#password');
        var confirmInput = $('#password_confirmation');
        
        if ($(this).is(':checked')) {
            passwordFields.slideDown();
            passwordInput.prop('required', true);
            confirmInput.prop('required', true);
        } else {
            passwordFields.slideUp();
            passwordInput.prop('required', false);
            confirmInput.prop('required', false);
            passwordInput.val('');
            confirmInput.val('');
        }
    });

    // Prevent Enter key from submitting form on input fields
    $('#user-edit-form input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            return false;
        }
    });

    // Initialize Select2 if available
    if ($.fn.select2) {
        $('#role_id, #division_id, #location_id').select2({
            placeholder: 'Select an option...',
            allowClear: true,
            width: '100%'
        });
    }
});
</script>
@endpush
