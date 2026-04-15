@extends('layouts.auth')

@section('htmlheader_title')
    Reset Password - IMS Quty Karunia
@endsection

@section('main-content')

<body class="hold-transition login-page" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <div class="login-box" style="width: 400px; margin: 5% auto;">
        
        <!-- Logo Card -->
        <div style="background: white; border-radius: 15px; padding: 25px; margin-bottom: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center;">
            <i class="fas fa-lock-open" style="font-size: 48px; color: #667eea; margin-bottom: 15px;"></i>
            <h2 style="margin: 0 0 5px 0; font-weight: 600;">
                <span style="color: #667eea;">Reset</span> <span style="color: #764ba2;">Password</span>
            </h2>
            <p style="margin: 0; color: #666; font-size: 14px;">Create your new secure password</p>
        </div>

        <!-- Success Alert -->
        @if (session('status'))
            <div class="alert alert-success" style="border-radius: 10px; border: none; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2); margin-bottom: 20px; background: #d4edda; border-left: 4px solid #28a745;">
                <i class="fas fa-check-circle" style="margin-right: 8px;"></i>
                <strong style="color: #155724;">Success!</strong>
                <p style="margin: 5px 0 0 0; color: #155724;">{{ session('status') }}</p>
            </div>
        @endif

        <!-- Error Alert -->
        @if (count($errors) > 0)
            <div class="alert alert-danger" style="border-radius: 10px; border: none; box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2); margin-bottom: 20px; background: #f8d7da; border-left: 4px solid #dc3545;">
                <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
                <strong style="color: #721c24;">Error!</strong>
                <ul style="margin: 5px 0 0 0; padding-left: 20px; color: #721c24;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Reset Form Card -->
        <div class="login-box-body" style="background: white; border-radius: 15px; padding: 40px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); border: none;">
            
            <h3 style="margin: 0 0 10px 0; color: #333; font-weight: 600; font-size: 20px;">Create New Password</h3>
            <p style="margin: 0 0 30px 0; color: #666; font-size: 14px; line-height: 1.6;">
                Your password reset link is valid. Enter your new password below.
            </p>

            <form id="resetForm" action="{{ url('/password/reset') }}" method="post">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                
                <!-- Email Field -->
                <div class="form-group" style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; color: #555; font-weight: 500; font-size: 14px;">
                        <i class="fas fa-envelope" style="color: #667eea; margin-right: 5px;"></i>Email Address
                    </label>
                    <input type="email" 
                           class="form-control" 
                           name="email" 
                           id="email"
                           value="{{ $email ?? old('email') }}"
                           required
                           readonly
                           style="width: 100%; height: 50px; border-radius: 10px; border: 2px solid #e1e5e9; padding: 0 20px; font-size: 14px; transition: all 0.3s ease; background-color: #f8f9fa; cursor: not-allowed;"/>
                    <small style="display: block; margin-top: 5px; color: #999; font-size: 12px;">
                        <i class="fas fa-info-circle"></i> Email address from reset link
                    </small>
                    @error('email')
                        <span style="display: block; margin-top: 5px; color: #dc3545; font-size: 12px;">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                <!-- New Password Field -->
                <div class="form-group" style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; color: #555; font-weight: 500; font-size: 14px;">
                        <i class="fas fa-lock" style="color: #667eea; margin-right: 5px;"></i>New Password
                    </label>
                    <div style="position: relative;">
                        <input type="password" 
                               class="form-control" 
                               name="password" 
                               id="password"
                               required
                               minlength="8"
                               style="width: 100%; height: 50px; border-radius: 10px; border: 2px solid #e1e5e9; padding: 0 45px 0 20px; font-size: 14px; transition: all 0.3s ease;"
                               onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'"
                               onblur="this.style.borderColor='#e1e5e9'; this.style.boxShadow='none'"
                               oninput="checkPasswordStrength()"/>
                        <button type="button" 
                                onclick="togglePasswordVisibility('password')" 
                                style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; cursor: pointer; padding: 0; font-size: 16px;">
                            <i class="fas fa-eye" id="togglePassword"></i>
                        </button>
                    </div>
                    <div id="passwordStrength" style="margin-top: 8px; display: none;">
                        <div style="height: 4px; background: #e1e5e9; border-radius: 2px; overflow: hidden;">
                            <div id="strengthBar" style="height: 100%; width: 0%; transition: all 0.3s ease;"></div>
                        </div>
                        <small id="strengthText" style="display: block; margin-top: 5px; font-size: 12px;"></small>
                    </div>
                    <small style="display: block; margin-top: 5px; color: #999; font-size: 12px;">
                        <i class="fas fa-shield-alt"></i> Minimum 8 characters, include uppercase, lowercase, numbers & symbols
                    </small>
                    @error('password')
                        <span style="display: block; margin-top: 5px; color: #dc3545; font-size: 12px;">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                <!-- Confirm Password Field -->
                <div class="form-group" style="margin-bottom: 30px;">
                    <label style="display: block; margin-bottom: 8px; color: #555; font-weight: 500; font-size: 14px;">
                        <i class="fas fa-lock" style="color: #667eea; margin-right: 5px;"></i>Confirm New Password
                    </label>
                    <div style="position: relative;">
                        <input type="password" 
                               class="form-control" 
                               name="password_confirmation" 
                               id="password_confirmation"
                               required
                               minlength="8"
                               style="width: 100%; height: 50px; border-radius: 10px; border: 2px solid #e1e5e9; padding: 0 45px 0 20px; font-size: 14px; transition: all 0.3s ease;"
                               onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'"
                               onblur="this.style.borderColor='#e1e5e9'; this.style.boxShadow='none'"
                               oninput="checkPasswordMatch()"/>
                        <button type="button" 
                                onclick="togglePasswordVisibility('password_confirmation')" 
                                style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; cursor: pointer; padding: 0; font-size: 16px;">
                            <i class="fas fa-eye" id="togglePasswordConfirmation"></i>
                        </button>
                    </div>
                    <small id="passwordMatch" style="display: block; margin-top: 5px; font-size: 12px;"></small>
                    @error('password_confirmation')
                        <span style="display: block; margin-top: 5px; color: #dc3545; font-size: 12px;">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        id="resetBtn"
                        class="btn btn-primary btn-block"
                        style="width: 100%; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); margin-bottom: 20px;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.4)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.3)'">
                    <i class="fas fa-check-circle" style="margin-right: 8px;"></i><span id="resetText">Reset Password</span>
                    <i class="fas fa-spinner fa-spin" id="resetSpinner" style="margin-left: 8px; display: none;"></i>
                </button>

                <!-- Back to Login -->
                <div style="text-align: center; padding-top: 20px; border-top: 1px solid #eee;">
                    <a href="{{ url('/login') }}" 
                       style="color: #667eea; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; font-size: 14px; transition: color 0.3s ease;"
                       onmouseover="this.style.color='#764ba2'"
                       onmouseout="this.style.color='#667eea'">
                        <i class="fas fa-arrow-left" style="margin-right: 5px;"></i>Back to Login
                    </a>
                </div>

            </form>

            <!-- Security Info -->
            <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee; text-align: center;">
                <p style="margin: 0 0 10px 0; color: #999; font-size: 12px;">
                    <i class="fas fa-shield-alt" style="color: #667eea;"></i>
                    Your password will be encrypted and stored securely
                </p>
                <p style="margin: 0; color: #999; font-size: 12px;">
                    <i class="fas fa-clock" style="color: #667eea;"></i>
                    This reset link expires after use or 60 minutes
                </p>
            </div>

        </div><!-- /.login-box-body -->

    </div><!-- /.login-box -->

    <!-- Loading Overlay -->
    <div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.9); z-index: 9999; justify-content: center; align-items: center;">
        <div style="text-align: center;">
            <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #667eea; margin-bottom: 15px;"></i>
            <p style="color: #667eea; font-weight: 500;">Resetting password...</p>
        </div>
    </div>

    @include('layouts.partials.scripts_auth')

    <script>
        $(document).ready(function() {
            // Form validation and submission
            $('#resetForm').on('submit', function(e) {
                const password = $('#password').val();
                const confirmation = $('#password_confirmation').val();
                
                if (password.length < 8) {
                    e.preventDefault();
                    showAlert('error', 'Password must be at least 8 characters long.');
                    return false;
                }
                
                if (password !== confirmation) {
                    e.preventDefault();
                    showAlert('error', 'Passwords do not match.');
                    return false;
                }
                
                // Show loading state
                showLoading();
            });
            
            // Auto-focus password field
            $('#password').focus();
        });
        
        // Toggle password visibility
        function togglePasswordVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById('toggle' + fieldId.charAt(0).toUpperCase() + fieldId.slice(1));
            
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
        
        // Check password strength
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            const strengthContainer = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthContainer.style.display = 'none';
                return;
            }
            
            strengthContainer.style.display = 'block';
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            const colors = ['#dc3545', '#ffc107', '#17a2b8', '#28a745', '#155724'];
            const texts = ['Very Weak', 'Weak', 'Fair', 'Strong', 'Very Strong'];
            const widths = ['20%', '40%', '60%', '80%', '100%'];
            
            strengthBar.style.backgroundColor = colors[strength - 1];
            strengthBar.style.width = widths[strength - 1];
            strengthText.textContent = texts[strength - 1];
            strengthText.style.color = colors[strength - 1];
        }
        
        // Check password match
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmation = document.getElementById('password_confirmation').value;
            const matchText = document.getElementById('passwordMatch');
            
            if (confirmation.length === 0) {
                matchText.textContent = '';
                return;
            }
            
            if (password === confirmation) {
                matchText.innerHTML = '<i class="fas fa-check-circle"></i> Passwords match';
                matchText.style.color = '#28a745';
            } else {
                matchText.innerHTML = '<i class="fas fa-times-circle"></i> Passwords do not match';
                matchText.style.color = '#dc3545';
            }
        }
        
        // Show loading overlay
        function showLoading() {
            $('#resetText').text('Resetting...');
            $('#resetSpinner').show();
            $('#resetBtn').prop('disabled', true);
            $('#loadingOverlay').css('display', 'flex');
        }
        
        // Show alert
        function showAlert(type, message) {
            const alertClass = type === 'error' ? 'alert-danger' : 'alert-info';
            const icon = type === 'error' ? 'exclamation-triangle' : 'info-circle';
            
            const alert = $('<div>')
                .addClass('alert ' + alertClass)
                .css({
                    'border-radius': '10px',
                    'border': 'none',
                    'box-shadow': '0 4px 15px rgba(220, 53, 69, 0.2)',
                    'margin-bottom': '20px',
                    'background': type === 'error' ? '#f8d7da' : '#d1ecf1',
                    'border-left': '4px solid ' + (type === 'error' ? '#dc3545' : '#17a2b8')
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

