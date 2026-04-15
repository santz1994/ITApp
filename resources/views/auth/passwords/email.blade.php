@extends('layouts.auth')

@section('htmlheader_title')
    Password Recovery - IMS Quty Karunia
@endsection

@section('main-content')

<body class="hold-transition login-page" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <div class="login-box" style="width: 400px; margin: 5% auto;">
        <!-- Logo Section -->
        <div class="login-logo" style="margin-bottom: 30px; text-align: center;">
            <div style="background: white; border-radius: 15px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 20px;">
                <i class="fas fa-key" style="font-size: 48px; color: #667eea; margin-bottom: 10px;"></i>
                <h2 style="color: #333; margin: 0; font-weight: 700;">
                    <span style="color: #667eea;">Password</span> <span style="color: #764ba2;">Recovery</span>
                </h2>
                <p style="color: #666; margin: 5px 0 0 0; font-size: 14px;">Reset your account password</p>
            </div>
        </div>

        <!-- Alert Messages -->
        @if (session('status'))
            <div class="alert alert-success" style="border-radius: 10px; border: none; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2); background: #d4edda; border-left: 4px solid #28a745;">
                <i class="fas fa-check-circle" style="margin-right: 8px; color: #28a745;"></i>
                <strong style="color: #155724;">Success!</strong>
                <p style="color: #155724; margin: 5px 0 0 0;">{{ session('status') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" style="border-radius: 10px; border: none; box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2); background: #f8d7da; border-left: 4px solid #dc3545;">
                <i class="fas fa-exclamation-triangle" style="margin-right: 8px; color: #dc3545;"></i>
                <strong style="color: #721c24;">Error!</strong>
                <ul style="margin: 10px 0 0 0; color: #721c24;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Password Reset Form -->
        <div class="login-box-body" style="background: white; border-radius: 15px; padding: 40px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); border: none;">
            <div style="text-align: center; margin-bottom: 30px;">
                <h3 style="color: #333; font-weight: 600; margin-bottom: 8px;">Forgot Your Password?</h3>
                <p style="color: #666; margin: 0; font-size: 14px; line-height: 1.6;">
                    No worries! Enter your email address and we'll send you a link to reset your password.
                </p>
            </div>

            <form action="{{ url('/password/email') }}" method="post" id="resetForm" novalidate>
                @csrf

                <!-- Email Field -->
                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="email" style="color: #555; font-weight: 500; margin-bottom: 8px; display: block;">
                        <i class="fas fa-envelope" style="margin-right: 8px; color: #667eea;"></i>Email Address
                    </label>
                    <input type="email" 
                           class="form-control" 
                           id="email"
                           name="email" 
                           placeholder="Enter your registered email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           style="height: 50px; border-radius: 10px; border: 2px solid #e1e5e9; padding: 0 20px; font-size: 16px; transition: all 0.3s ease;"
                           onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'"
                           onblur="this.style.borderColor='#e1e5e9'; this.style.boxShadow='none'"/>
                    <small style="color: #999; font-size: 12px; margin-top: 5px; display: block;">
                        <i class="fas fa-info-circle" style="margin-right: 5px;"></i>
                        We'll email you a secure link to reset your password
                    </small>
                    @if ($errors->has('email'))
                        <span style="color: #dc3545; font-size: 13px; margin-top: 5px; display: block;">
                            <i class="fas fa-exclamation-circle"></i> {{ $errors->first('email') }}
                        </span>
                    @endif
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="btn btn-block" 
                        id="resetBtn"
                        style="height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 10px; color: white; font-size: 16px; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); margin-bottom: 20px;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.4)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.3)'">
                    <i class="fas fa-paper-plane" style="margin-right: 8px;"></i>
                    <span id="resetText">Send Reset Link</span>
                    <i class="fas fa-spinner fa-spin" id="resetSpinner" style="display: none; margin-left: 8px;"></i>
                </button>

                <!-- Back to Login -->
                <div style="text-align: center; padding-top: 20px; border-top: 1px solid #eee;">
                    <a href="{{ url('/login') }}" 
                       style="color: #667eea; text-decoration: none; font-size: 14px; font-weight: 500; display: inline-flex; align-items: center; transition: all 0.3s ease;"
                       onmouseover="this.style.color='#764ba2'"
                       onmouseout="this.style.color='#667eea'">
                        <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>
                        Back to Login
                    </a>
                </div>
            </form>

            <!-- Help Info -->
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                <p style="color: #999; font-size: 12px; margin: 0;">
                    <i class="fas fa-question-circle" style="margin-right: 5px;"></i>
                    Having trouble? Contact your system administrator
                </p>
                <p style="color: #999; font-size: 12px; margin: 5px 0 0 0;">
                    <i class="fas fa-shield-alt" style="margin-right: 5px;"></i>
                    Password reset links expire after 60 minutes
                </p>
            </div>
        </div><!-- /.login-box-body -->

    </div><!-- /.login-box -->

    <!-- Loading Overlay -->
    <div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.9); z-index: 9999; justify-content: center; align-items: center;">
        <div style="text-align: center;">
            <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #667eea; margin-bottom: 15px;"></i>
            <p style="color: #667eea; font-weight: 500;">Sending reset link...</p>
        </div>
    </div>

    @include('layouts.partials.scripts_auth')

    <script>
        // Enhanced password reset form functionality
        $(document).ready(function() {
            // Form validation and submission
            $('#resetForm').on('submit', function(e) {
                const email = $('#email').val().trim();
                
                if (!email) {
                    e.preventDefault();
                    showAlert('error', 'Please enter your email address.');
                    return false;
                }
                
                if (!isValidEmail(email)) {
                    e.preventDefault();
                    showAlert('error', 'Please enter a valid email address.');
                    return false;
                }
                
                // Show loading state
                showLoading();
            });
            
            // Auto-focus email field
            $('#email').focus();
            
            // Auto-dismiss success alert after 10 seconds
            if ($('.alert-success').length) {
                setTimeout(function() {
                    $('.alert-success').fadeOut('slow');
                }, 10000);
            }
        });
        
        // Email validation
        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
        
        // Show loading overlay
        function showLoading() {
            $('#resetText').text('Sending...');
            $('#resetSpinner').show();
            $('#resetBtn').prop('disabled', true);
            $('#loadingOverlay').css('display', 'flex');
        }
        
        // Show alert (for validation errors)
        function showAlert(type, message) {
            const alertClass = type === 'error' ? 'alert-danger' : 'alert-info';
            const icon = type === 'error' ? 'exclamation-triangle' : 'info-circle';
            
            const alert = $('<div>')
                .addClass('alert ' + alertClass)
                .css({
                    'border-radius': '10px',
                    'border': 'none',
                    'box-shadow': '0 4px 15px rgba(220, 53, 69, 0.2)',
                    'margin-bottom': '20px'
                })
                .html('<i class="fas fa-' + icon + '" style="margin-right: 8px;"></i>' + message);
            
            $('.login-box').prepend(alert);
            
            setTimeout(function() {
                alert.fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
        }
    </script>
</body>

@endsection

