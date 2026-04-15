@extends('layouts.app')

@section('main-content')

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Create New User',
    'subtitle' => 'Add a new user to the system',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Users', 'url' => route('users.index')],
        ['label' => 'Create']
    ],
    'actions' => '
        <a href="'.route('users.index').'" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> <span class="hidden-xs">Back</span>
        </a>
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

    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-user-plus"></i> User Information
                    </h3>
                </div>
                <form method="POST" action="{{ route('users.store') }}" id="user-create-form">
                    @csrf
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
                                       value="{{ old('name') }}"
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
                                       value="{{ old('email') }}"
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
                                       value="{{ old('employee_id') }}"
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
                                    @if(isset($roles) && $roles->count() > 0)
                                        @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('-', ' ', $role->name)) }}
                                        </option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>No roles available</option>
                                    @endif
                                </select>
                                <small class="help-text">Determines what actions the user can perform (see sidebar for details)</small>
                                @error('role_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password">
                                    <i class="fa fa-lock text-warning"></i> Password <span class="text-red">*</span>
                                </label>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password"
                                       placeholder="Minimum 8 characters" 
                                       required>
                                <small class="help-text">Must be at least 8 characters long</small>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation">
                                    <i class="fa fa-lock text-warning"></i> Confirm Password <span class="text-red">*</span>
                                </label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation"
                                       placeholder="Re-enter password" 
                                       required>
                                <small class="help-text">Must match the password above</small>
                            </div>

                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}> 
                                    <i class="fa fa-check-circle text-success"></i> Active Account
                                </label>
                                <small class="help-text d-block">Uncheck to create the user in inactive state (they won't be able to log in)</small>
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
                                    @endphp
                                    @foreach($divs as $div)
                                        <option value="{{ $div->id }}" {{ old('division_id') == $div->id ? 'selected' : '' }}>{{ $div->name }}</option>
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
                                    @endphp
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
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
                                       value="{{ old('position') }}"
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
                                       value="{{ old('phone') }}"
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
                                       value="{{ old('mobile') }}"
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
                                       value="{{ old('extension') }}"
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
                            <i class="fa fa-save"></i> <b>Create User</b>
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-default btn-lg">
                            <i class="fa fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-md-4">
            {{-- Role Information --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-info-circle"></i> Role Descriptions
                    </h3>
                </div>
                <div class="box-body">
                    <p><strong>Available Roles:</strong></p>
                    <ul style="list-style: none; padding-left: 0;">
                        <li style="margin-bottom: 12px;">
                            <span class="label label-danger">Super Admin</span>
                            <p style="margin-top: 5px; font-size: 12px;">Full system access including user management and system settings</p>
                        </li>
                        <li style="margin-bottom: 12px;">
                            <span class="label label-warning">Admin</span>
                            <p style="margin-top: 5px; font-size: 12px;">Can manage assets, tickets, and users but cannot modify system settings</p>
                        </li>
                        <li style="margin-bottom: 12px;">
                            <span class="label label-info">Management</span>
                            <p style="margin-top: 5px; font-size: 12px;">Can view reports, KPI dashboard, and has oversight capabilities</p>
                        </li>
                        <li style="margin-bottom: 12px;">
                            <span class="label label-success">User</span>
                            <p style="margin-top: 5px; font-size: 12px;">Basic access to view and create tickets, view assigned assets</p>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Password Requirements --}}
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-key"></i> Password Requirements
                    </h3>
                </div>
                <div class="box-body">
                    <p><strong>Security Guidelines:</strong></p>
                    <ul>
                        <li><i class="fa fa-check text-success"></i> Minimum 8 characters</li>
                        <li><i class="fa fa-check text-success"></i> Must be confirmed</li>
                        <li><i class="fa fa-check text-success"></i> Avoid common passwords</li>
                        <li><i class="fa fa-info-circle text-info"></i> User will be required to change password on first login</li>
                    </ul>
                </div>
            </div>

            {{-- Quick Tips --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-lightbulb"></i> Quick Tips
                    </h3>
                </div>
                <div class="box-body">
                    <ul style="font-size: 13px; margin-bottom: 0;">
                        <li><strong>Email:</strong> Used for login and notifications</li>
                        <li><strong>Role:</strong> Determines permissions and access</li>
                        <li><strong>Division:</strong> Required for reporting</li>
                        <li><strong>Active Status:</strong> Inactive users cannot log in</li>
                        <li><strong>Location:</strong> Helps with asset assignment</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Loading Overlay --}}
@include('components.loading-overlay')

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Form loading state
    $('#user-create-form').on('submit', function() {
        showLoading('Creating user...');
    });

    // Prevent Enter key from submitting form on input fields
    $('#user-create-form input').on('keypress', function(e) {
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
