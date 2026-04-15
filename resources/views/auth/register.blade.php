@extends('layouts.auth')

@section('htmlheader_title')
    Register - IMS Quty Karunia
@endsection

@section('main-content')

<body class="hold-transition register-page" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <div class="register-box" style="width: 440px; margin: 3% auto;">
        
        <!-- Logo Card -->
        <div style="background: white; border-radius: 15px; padding: 25px; margin-bottom: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center;">
            <i class="fas fa-user-plus" style="font-size: 48px; color: #667eea; margin-bottom: 15px;"></i>
            <h2 style="margin: 0 0 5px 0; font-weight: 600;">
                <span style="color: #667eea;">Create</span> <span style="color: #764ba2;">Account</span>
            </h2>
            <p style="margin: 0; color: #666; font-size: 14px;">Join IMS Quty Karunia to manage resources</p>
        </div>

        <!-- Registration Form Card -->
        <div class="register-box-body" style="background: white; border-radius: 15px; padding: 40px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); border: none;">

            <!-- Error Alert -->
            @if ($errors->any())
                <div class="alert alert-danger" style="border-radius: 10px; border: none; box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2); margin-bottom: 25px; background: #f8d7da; border-left: 4px solid #dc3545;">
                    <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
                    <strong style="color: #721c24;">Validation Errors!</strong>
                    <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #721c24;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="registerForm" action="{{ url('/register') }}" method="post">
                @csrf

                <!-- Full Name -->
                <div class="form-group" style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; color: #555; font-weight: 500; font-size: 14px;">
                        <i class="fas fa-user" style="color: #667eea; margin-right: 5px;"></i>Full Name
                    </label>
                    <input id="name" 
                           type="text" 
                           name="name" 
                           class="form-control" 
                           value="{{ old('name') }}" 
                           required 
                           autofocus
                           style="width: 100%; height: 50px; border-radius: 10px; border: 2px solid {{ $errors->has('name') ? '#dc3545' : '#e1e5e9' }}; padding: 0 20px; font-size: 14px; transition: all 0.3s ease;"
                           onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'"
                           onblur="this.style.borderColor='{{ $errors->has('name') ? '#dc3545' : '#e1e5e9' }}'; this.style.boxShadow='none'"/>
                    <small style="display: block; margin-top: 5px; color: #999; font-size: 12px;">
                        <i class="fas fa-info-circle"></i> Your full name as it appears on official documents
                    </small>
                    @error('name')
                        <span style="display: block; margin-top: 5px; color: #dc3545; font-size: 12px;">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                <!-- Email Address -->
                <div class="form-group" style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; color: #555; font-weight: 500; font-size: 14px;">
                        <i class="fas fa-envelope" style="color: #667eea; margin-right: 5px;"></i>Email Address
                    </label>
                    <input id="email" 
                           type="email" 
                           name="email" 
                           class="form-control" 
                           value="{{ old('email') }}" 
                           required
                           style="width: 100%; height: 50px; border-radius: 10px; border: 2px solid {{ $errors->has('email') ? '#dc3545' : '#e1e5e9' }}; padding: 0 20px; font-size: 14px; transition: all 0.3s ease;"
                           onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'"
                           onblur="this.style.borderColor='{{ $errors->has('email') ? '#dc3545' : '#e1e5e9' }}'; this.style.boxShadow='none'"
                           oninput="checkEmailAvailability()"/>
                    <small id="emailCheck" style="display: block; margin-top: 5px; font-size: 12px; color: #999;">
                        <i class="fas fa-info-circle"></i> Use your company or organizational email
                    </small>
                    @error('email')
                        <span style="display: block; margin-top: 5px; color: #dc3545; font-size: 12px;">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group" style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; color: #555; font-weight: 500; font-size: 14px;">
                        <i class="fas fa-lock" style="color: #667eea; margin-right: 5px;"></i>Password
                    </label>
                    <div style="position: relative;">
                        <input id="password" 
                               type="password" 
                               name="password" 
                               class="form-control" 
                               required
                               minlength="8"
                               style="width: 100%; height: 50px; border-radius: 10px; border: 2px solid {{ $errors->has('password') ? '#dc3545' : '#e1e5e9' }}; padding: 0 45px 0 20px; font-size: 14px; transition: all 0.3s ease;"
                               onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'"
                               onblur="this.style.borderColor='{{ $errors->has('password') ? '#dc3545' : '#e1e5e9' }}'; this.style.boxShadow='none'"
                               oninput="checkPasswordStrength()"/>
                        <button type="button" 
                                onclick="togglePassword('password', 'togglePasswordIcon')" 
                                style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; cursor: pointer; padding: 0; font-size: 16px;">
                            <i class="fas fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                    <div id="passwordStrength" style="margin-top: 8px; display: none;">
                        <div style="height: 4px; background: #e1e5e9; border-radius: 2px; overflow: hidden;">
                            <div id="strengthBar" style="height: 100%; width: 0%; transition: all 0.3s ease;"></div>
                        </div>
                        <small id="strengthText" style="display: block; margin-top: 5px; font-size: 12px;"></small>
                    </div>
                    <small style="display: block; margin-top: 5px; color: #999; font-size: 12px;">
                        <i class="fas fa-shield-alt"></i> Minimum 8 characters with uppercase, lowercase, numbers & symbols
                    </small>
                    @error('password')
                        <span style="display: block; margin-top: 5px; color: #dc3545; font-size: 12px;">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="form-group" style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; color: #555; font-weight: 500; font-size: 14px;">
                        <i class="fas fa-lock" style="color: #667eea; margin-right: 5px;"></i>Confirm Password
                    </label>
                    <div style="position: relative;">
                        <input id="password_confirmation" 
                               type="password" 
                               name="password_confirmation" 
                               class="form-control" 
                               required
                               minlength="8"
                               style="width: 100%; height: 50px; border-radius: 10px; border: 2px solid #e1e5e9; padding: 0 45px 0 20px; font-size: 14px; transition: all 0.3s ease;"
                               onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'"
                               onblur="this.style.borderColor='#e1e5e9'; this.style.boxShadow='none'"
                               oninput="checkPasswordMatch()"/>
                        <button type="button" 
                                onclick="togglePassword('password_confirmation', 'togglePasswordConfirmIcon')" 
                                style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; cursor: pointer; padding: 0; font-size: 16px;">
                            <i class="fas fa-eye" id="togglePasswordConfirmIcon"></i>
                        </button>
                    </div>
                    <small id="passwordMatch" style="display: block; margin-top: 5px; font-size: 12px;"></small>
                </div>

                <!-- Terms Checkbox -->
                <div class="form-group" style="margin-bottom: 30px;">
                    <label style="display: flex; align-items: center; cursor: pointer; user-select: none;">
                        <input type="checkbox" 
                               name="terms" 
                               id="terms"
                               required
                               style="width: 18px; height: 18px; margin-right: 10px; cursor: pointer; accent-color: #667eea;"/>
                        <span style="color: #555; font-size: 14px;">
                            I agree to the <a href="{{ url('/terms') }}" target="_blank" style="color: #667eea; text-decoration: none; font-weight: 500;">Terms of Service</a> and <a href="{{ url('/terms') }}" target="_blank" style="color: #667eea; text-decoration: none; font-weight: 500;">Privacy Policy</a>
                        </span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        id="registerBtn"
                        class="btn btn-primary btn-block"
                        style="width: 100%; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); margin-bottom: 20px;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.4)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.3)'">
                    <i class="fas fa-user-plus" style="margin-right: 8px;"></i><span id="registerText">Create Account</span>
                    <i class="fas fa-spinner fa-spin" id="registerSpinner" style="margin-left: 8px; display: none;"></i>
                </button>

                <!-- Back to Login -->
                <div style="text-align: center; padding-top: 20px; border-top: 1px solid #eee;">
                    <a href="{{ url('/login') }}" 
                       style="color: #667eea; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; font-size: 14px; transition: color 0.3s ease;"
                       onmouseover="this.style.color='#764ba2'"
                       onmouseout="this.style.color='#667eea'">
                        <i class="fas fa-arrow-left" style="margin-right: 5px;"></i>Already have an account? Sign in
                    </a>
                </div>

            </form>

        </div><!-- /.register-box-body -->

    </div><!-- /.register-box -->

    <!-- Loading Overlay -->
    <div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.9); z-index: 9999; justify-content: center; align-items: center;">
        <div style="text-align: center;">
            <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #667eea; margin-bottom: 15px;"></i>
            <p style="color: #667eea; font-weight: 500;">Creating your account...</p>
        </div>
    </div>

    @include('layouts.partials.scripts_auth')

    <script>
        $(document).ready(function() {
            // Form validation and submission
            $('#registerForm').on('submit', function(e) {
                const name = $('#name').val().trim();
                const email = $('#email').val().trim();
                const password = $('#password').val();
                const confirmation = $('#password_confirmation').val();
                const terms = $('#terms').is(':checked');
                
                if (!name || !email || !password || !confirmation) {
                    e.preventDefault();
                    showAlert('error', 'Please fill in all required fields.');
                    return false;
                }
                
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
                
                if (!terms) {
                    e.preventDefault();
                    showAlert('error', 'You must agree to the Terms of Service.');
                    return false;
                }
                
                // Show loading state
                showLoading();
            });
            
            // Auto-focus name field
            $('#name').focus();
        });
        
        // Toggle password visibility
        function togglePassword(fieldId, iconId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);
            
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
        
        // Check email availability (mock - implement actual check if needed)
        function checkEmailAvailability() {
            const email = document.getElementById('email').value;
            const emailCheck = document.getElementById('emailCheck');
            
            if (email.length === 0) {
                emailCheck.innerHTML = '<i class="fas fa-info-circle"></i> Use your company or organizational email';
                emailCheck.style.color = '#999';
                return;
            }
            
            // Add actual AJAX check here if needed
            if (email.length > 0 && email.includes('@')) {
                emailCheck.innerHTML = '<i class="fas fa-check-circle"></i> Email format is valid';
                emailCheck.style.color = '#28a745';
            }
        }
        
        // Show loading overlay
        function showLoading() {
            $('#registerText').text('Creating...');
            $('#registerSpinner').show();
            $('#registerBtn').prop('disabled', true);
            $('#loadingOverlay').css('display', 'flex');
        }
        
        // Show alert
        function showAlert(type, message) {
            const alertClass = type === 'error' ? 'alert-danger' : 'alert-info';
            const icon = type === 'error' ? 'exclamation-triangle' : 'info-circle';
            const bgColor = type === 'error' ? '#f8d7da' : '#d1ecf1';
            const borderColor = type === 'error' ? '#dc3545' : '#17a2b8';
            
            const alert = $('<div>')
                .addClass('alert ' + alertClass)
                .css({
                    'border-radius': '10px',
                    'border': 'none',
                    'box-shadow': '0 4px 15px rgba(220, 53, 69, 0.2)',
                    'margin-bottom': '20px',
                    'background': bgColor,
                    'border-left': '4px solid ' + borderColor
                })
                .html('<i class="fas fa-' + icon + '" style="margin-right: 8px;"></i>' + message);
            
            $('.register-box-body').prepend(alert);
            
            setTimeout(function() {
                alert.fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
        }
    </script>
</body>

@endsection

