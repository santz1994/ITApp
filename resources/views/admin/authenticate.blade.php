@extends('layouts.app')

@section('page_title')
    Admin Authentication Required
@endsection

@section('page_description')
    Security verification for administrative operations
@endsection

@section('main-content')
<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-shield"></i> Administrative Security Check
                </h3>
            </div>
            
            @if($canEdit)
            <form method="POST" action="{{ route('admin.process-auth') }}">
                @csrf
                <input type="hidden" name="intended" value="{{ $intended }}">
                <input type="hidden" name="action" value="{{ $action }}">
                <input type="hidden" name="module" value="{{ $module }}">
                
                <div class="box-body">
                    <div class="alert alert-warning">
                        <h4><i class="icon fa fa-warning"></i> Restricted Administrative Access</h4>
                        You are attempting to access: <strong>{{ $module }}</strong>
                        <br>Action: <strong>{{ ucwords(str_replace('_', ' ', $action)) }}</strong>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <h4><i class="icon fa fa-ban"></i> Authentication Failed</h4>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <h4><i class="icon fa fa-info"></i> Security Requirements</h4>
                        <ul class="mb-0">
                            <li><strong>Authorization:</strong> Super Admin role + daniel@quty.co.id email</li>
                            <li><strong>Authentication:</strong> Password confirmation required</li>
                            <li><strong>Session:</strong> Valid for 30 minutes after confirmation</li>
                            <li><strong>Logging:</strong> All administrative actions are logged</li>
                            <li><strong>Audit:</strong> IP address and timestamps recorded</li>
                        </ul>
                    </div>

                    <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                        <label for="password">
                            <i class="fa fa-key"></i> Confirm Your Password
                        </label>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               class="form-control input-lg" 
                               placeholder="Enter your current account password"
                               required 
                               autofocus>
                        @if($errors->has('password'))
                            <span class="help-block">{{ $errors->first('password') }}</span>
                        @endif
                        <small class="help-block">
                            <i class="fa fa-info-circle"></i> Enter the password for your account: {{ auth()->user()->email }}
                        </small>
                    </div>

                    <div class="form-group {{ $errors->has('acknowledge') ? 'has-error' : '' }}">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="acknowledge" value="1" required> 
                                <strong>I acknowledge that:</strong>
                            </label>
                        </div>
                        <ul class="list-unstyled" style="margin-left: 20px; font-size: 12px;">
                            <li>• Administrative operations can have system-wide impact</li>
                            <li>• All my actions will be logged and audited</li>
                            <li>• I am responsible for any changes made under my account</li>
                            <li>• Unauthorized access or misuse may result in account suspension</li>
                        </ul>
                        @if($errors->has('acknowledge'))
                            <span class="help-block">{{ $errors->first('acknowledge') }}</span>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="callout callout-success">
                                <h5><i class="fa fa-check"></i> Authorized User</h5>
                                <p>{{ auth()->user()->name }}<br>
                                   {{ auth()->user()->email }}</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="callout callout-info">
                                <h5><i class="fa fa-clock-o"></i> Session Info</h5>
                                <p>IP: {{ request()->ip() }}<br>
                                   Time: {{ now()->format('Y-m-d H:i:s T') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="fa fa-unlock"></i> Authenticate & Proceed
                            </button>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-default btn-lg pull-right">
                                <i class="fa fa-arrow-left"></i> Cancel & Return
                            </a>
                        </div>
                    </div>
                </div>
            </form>
            @else
            <div class="box-body">
                <div class="alert alert-danger">
                    <h4><i class="icon fa fa-ban"></i> Access Denied</h4>
                    <p>Your account <strong>({{ auth()->user()->email }})</strong> is not authorized to perform administrative modifications.</p>
                    <p>Only <strong>daniel@quty.co.id</strong> can access administrative edit operations.</p>
                </div>
                
                <div class="alert alert-info">
                    <h4><i class="icon fa fa-info"></i> Available Actions</h4>
                    <p>You can still access the following with your Super Admin role:</p>
                    <ul>
                        <li>View database tables and records</li>
                        <li>Export data (CSV, Excel, PDF)</li>
                        <li>View system statistics and reports</li>
                        <li>Access read-only admin panels</li>
                    </ul>
                </div>
            </div>
            
            <div class="box-footer">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-default btn-lg">
                    <i class="fa fa-arrow-left"></i> Return to Dashboard
                </a>
            </div>
            @endif
        </div>

        <!-- Security Information Panel -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-shield"></i> Security Framework
                </h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-6">
                        <h5><strong>Access Control Levels:</strong></h5>
                        <ol>
                            <li><strong>Authentication:</strong> Valid user account</li>
                            <li><strong>Authorization:</strong> Super Admin role</li>
                            <li><strong>Email Restriction:</strong> daniel@quty.co.id for edits</li>
                            <li><strong>Password Confirmation:</strong> Recent authentication</li>
                        </ol>
                    </div>
                    <div class="col-sm-6">
                        <h5><strong>Security Features:</strong></h5>
                        <ul>
                            <li>Session timeout (30 minutes)</li>
                            <li>Complete action logging</li>
                            <li>IP address tracking</li>
                            <li>CSRF protection</li>
                            <li>Real-time monitoring</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
$(document).ready(function() {
    // Focus on password field
    $('#password').focus();
    
    // Auto-submit on Enter key
    $('#password').keypress(function(e) {
        if (e.which === 13 && $('input[name="acknowledge"]').is(':checked')) {
            $(this).closest('form').submit();
        }
    });
    
    // Show warning when typing password
    let warningShown = false;
    $('#password').on('input', function() {
        if (!warningShown && $(this).val().length > 0) {
            warningShown = true;
            $(this).after('<small class="text-warning"><i class="fa fa-warning"></i> Ensure you are in a secure environment</small>');
        }
    });
});
</script>
@endsection
@endsection