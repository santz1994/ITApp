@extends('layouts.auth')

@section('htmlheader_title')
    Log in - IMS Quty Karunia
@endsection

@section('main-content')
<body class="hold-transition login-page" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <div class="login-box" style="width: 400px; margin: 5% auto;">
        <!-- Logo Section -->
        <div class="login-logo" style="margin-bottom: 30px; text-align: center;">
            <div style="background: white; border-radius: 15px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 20px;">
                <i class="fas fa-tools" style="font-size: 48px; color: #667eea; margin-bottom: 10px;"></i>
                <h2 style="color: #333; margin: 0; font-weight: 700;">
                    <span style="color: #0a62a6;">IMS</span>
                </h2>
		<h1 style="color: #333; margin: 0; font-weight: 700;">
                    <span style="color: #0a62a6;">Quty Karunia</span>
                </h1>
            </div>
        </div>

        <!-- Alert Messages -->
        @if (count($errors) > 0)
            <div class="alert alert-danger" style="border-radius: 10px; border: none; box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2);">
                <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
                <strong>Login Failed!</strong> Please check your credentials.
                <ul style="margin-top: 10px; margin-bottom: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning" style="border-radius: 10px; border: none; box-shadow: 0 4px 15px rgba(255, 193, 7, 0.2);">
                <i class="fas fa-clock" style="margin-right: 8px;"></i>
                {{ session('warning') }}
            </div>
        @endif

        @if (session('message'))
            <div class="alert alert-info" style="border-radius: 10px; border: none; box-shadow: 0 4px 15px rgba(23, 162, 184, 0.2);">
                <i class="fas fa-info-circle" style="margin-right: 8px;"></i>
                {{ session('message') }}
            </div>
        @endif

        <!-- Login Form -->
        <div class="login-box-body" style="background: white; border-radius: 15px; padding: 40px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); border: none;">
            <div style="text-align: center; margin-bottom: 30px;">
                <h3 style="color: #333; font-weight: 600; margin-bottom: 8px;">Welcome Back!</h3>
                <p style="color: #666; margin: 0; font-size: 14px;">Sign in to access your IT Support dashboard</p>
            </div>

            <form action="{{ url('/login') }}" method="post" id="loginForm">
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
                           placeholder="Enter your email address"
                           value="{{ old('email') }}"
                           required
                           style="height: 50px; border-radius: 10px; border: 2px solid #e1e5e9; padding: 0 20px; font-size: 16px; transition: all 0.3s ease;"
                           onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'"
                           onblur="this.style.borderColor='#e1e5e9'; this.style.boxShadow='none'"/>
                </div>

                <!-- Password Field -->
                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="password" style="color: #555; font-weight: 500; margin-bottom: 8px; display: block;">
                        <i class="fas fa-lock" style="margin-right: 8px; color: #667eea;"></i>Password
                    </label>
                    <div style="position: relative;">
                        <input type="password" 
                               class="form-control" 
                               id="password"
                               name="password" 
                               placeholder="Enter your password"
                               required
                               autocomplete="current-password"
                               style="height: 50px; border-radius: 10px; border: 2px solid #e1e5e9; padding: 0 50px 0 20px; font-size: 16px; transition: all 0.3s ease;"
                               onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'"
                               onblur="this.style.borderColor='#e1e5e9'; this.style.boxShadow='none'"/>
                        <button type="button" onclick="togglePassword()" 
                                style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; cursor: pointer; padding: 5px;">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="row" style="margin-bottom: 10px; align-items: center;">
                    <div class="col-xs-6">
                        <div class="checkbox" style="margin: 0;">
                            <label style="color: #666; font-size: 14px; cursor: pointer;">
                                <input type="checkbox" name="remember" style="margin-right: 8px;"> 
                                <span style="vertical-align: middle;">Remember Me</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6" style="text-align: center;">
                        <p>
                            <a href="{{ url('/password/reset') }}" 
                           style="color: #667eea; text-decoration: none; font-size: 14px; font-weight: 500;">
                            Forgot Password?
                            </a>
                        </p>
                    </div>
                </div>


                <!-- Login Button -->
                <button type="submit" 
                        class="btn btn-block" 
                        id="loginBtn"
                        style="height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 10px; color: white; font-size: 16px; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.4)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.3)'">
                    <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i>
                    <span id="loginText">Sign In</span>
                    <i class="fas fa-spinner fa-spin" id="loginSpinner" style="display: none; margin-left: 8px;"></i>
                </button>
            </form>

            <!-- System Info -->
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                <p style="color: #999; font-size: 12px; margin: 0;">
                    <i class="fas fa-shield-alt" style="margin-right: 5px;"></i>
                    Secure login with session timeout ({{ config('session.lifetime') }} minutes)
                </p>
                <p style="color: #999; font-size: 12px; margin: 5px 0 0 0;">
                    <i class="fas fa-desktop" style="margin-right: 5px;"></i>
                    Single device access for enhanced security
                </p>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.9); z-index: 9999; justify-content: center; align-items: center;">
        <div style="text-align: center;">
            <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #667eea; margin-bottom: 15px;"></i>
            <p style="color: #667eea; font-weight: 500;">Signing you in...</p>
        </div>
    </div>

    @include('layouts.partials.scripts_auth')

    <script>
        // Enhanced login form functionality
        $(document).ready(function() {
            // Form validation and submission
            $('#loginForm').on('submit', function(e) {
                const email = $('#email').val().trim();
                const password = $('#password').val().trim();
                
                if (!email || !password) {
                    e.preventDefault();
                    showAlert('error', 'Please fill in all required fields.');
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
            
            // Auto-focus first input
            $('#email').focus();
            
            // Enter key navigation
            $('#email').on('keypress', function(e) {
                if (e.which === 13) {
                    $('#password').focus();
                }
            });
        });
        
        // Toggle password visibility
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash';
            } else {
                passwordField.type = 'password';
                toggleIcon.className = 'fas fa-eye';
            }
        }
        
        // Email validation
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        // Show loading state
        function showLoading() {
            document.getElementById('loginText').style.display = 'none';
            document.getElementById('loginSpinner').style.display = 'inline-block';
            document.getElementById('loginBtn').disabled = true;
            document.getElementById('loadingOverlay').style.display = 'flex';
        }
        
        // Show alert messages
        function showAlert(type, message) {
            const alertTypes = {
                'error': { class: 'alert-danger', icon: 'fas fa-exclamation-triangle' },
                'success': { class: 'alert-success', icon: 'fas fa-check-circle' },
                'warning': { class: 'alert-warning', icon: 'fas fa-exclamation-circle' },
                'info': { class: 'alert-info', icon: 'fas fa-info-circle' }
            };
            
            const alertInfo = alertTypes[type] || alertTypes.info;
            
            const alertHtml = `
                <div class="alert ${alertInfo.class}" style="border-radius: 10px; border: none; margin-bottom: 20px; animation: slideDown 0.3s ease;">
                    <i class="${alertInfo.icon}" style="margin-right: 8px;"></i>
                    ${message}
                </div>
            `;
            
            // Remove existing alerts
            $('.alert').remove();
            
            // Add new alert
            $('.login-box-body').prepend(alertHtml);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                $('.alert').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }
        
        // Session timeout warning
        let inactivityTimer;
        let warningTimer;
        const sessionTimeout = {{ config('session.lifetime') * 60 }}; // Convert to seconds
        const warningTime = sessionTimeout - 300; // Show warning 5 minutes before timeout
        
        function resetInactivityTimer() {
            clearTimeout(inactivityTimer);
            clearTimeout(warningTimer);
            
            // Set warning timer
            warningTimer = setTimeout(() => {
                if (confirm('Your session will expire in 5 minutes due to inactivity. Click OK to extend your session.')) {
                    // Make a request to extend session
                    fetch('/extend-session', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    }).then(() => {
                        resetInactivityTimer();
                    });
                }
            }, warningTime * 1000);
            
            // Set logout timer
            inactivityTimer = setTimeout(() => {
                alert('Your session has expired due to inactivity. You will be redirected to the login page.');
                window.location.href = '/login';
            }, sessionTimeout * 1000);
        }
        
        // Track user activity
        document.addEventListener('mousemove', resetInactivityTimer);
        document.addEventListener('keypress', resetInactivityTimer);
        document.addEventListener('click', resetInactivityTimer);
        document.addEventListener('scroll', resetInactivityTimer);
        
        // Initialize timer
        resetInactivityTimer();
    </script>
    
    <style>
        /* Animation keyframes */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive design */
        @media (max-width: 480px) {
            .login-box {
                width: 90% !important;
                margin: 20px auto !important;
            }
            
            .login-box-body {
                padding: 30px 25px !important;
            }
            
            .form-control {
                height: 45px !important;
                font-size: 16px !important;
            }
            
            button[type="submit"] {
                height: 45px !important;
                font-size: 15px !important;
            }
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #5a67d8;
        }
    </style>
</body>

@endsection