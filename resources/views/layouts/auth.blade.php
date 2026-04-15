<!DOCTYPE html>
<html>

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('htmlheader_title', 'IT Support System')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('favicon.png') }}">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="{{ asset('css/AdminLTE.min.css') }}">
    
    <!-- Custom Auth Styles -->
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .login-page {
            background-attachment: fixed;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        
        .login-page::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%);
            z-index: -1;
        }
        
        /* Remove default AdminLTE login styles */
        .login-box {
            margin: 0 auto;
        }
        
        .login-box-body {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        /* Form enhancements */
        .form-control:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            border-color: #667eea;
        }
        
        .alert {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Loading spinner */
        .spinner {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .login-box {
                width: 95% !important;
                margin: 10px auto !important;
            }
            
            .login-box-body {
                padding: 25px 20px !important;
            }
            
            .login-logo {
                margin-bottom: 20px !important;
            }
            
            .login-logo div {
                padding: 20px !important;
            }
            
            .login-logo h2 {
                font-size: 24px !important;
            }
            
            .login-logo i {
                font-size: 36px !important;
            }
        }
    </style>
    
    @yield('htmlheader_styles')
</head>

@yield('main-content')

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- AdminLTE JS -->
<script src="{{ asset('js/app.min.js') }}"></script>

@yield('htmlheader_scripts')

</html>

</html>