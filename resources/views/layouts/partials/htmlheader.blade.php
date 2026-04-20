<head>
    <meta charset="UTF-8">
    <title>QutyIT @if(isset($pageTitle))- @yield('htmlheader_title', $pageTitle) @endif</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Enterprise Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('favicon.png') }}">    
    <!-- Font Awesome Icons -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Custom CSS style -->
    <link href="{{ asset('/css/all.css') }}" rel="stylesheet" type="text/css" />    
    <!-- Responsive Fixes CSS -->
    <link href="{{ asset('/css/responsive-fix.css') }}" rel="stylesheet" type="text/css" />    
    <!-- Enhanced UX CSS -->
    <link href="{{ asset('/css/enhanced-ux.css') }}" rel="stylesheet" type="text/css" />
    <!-- Main header UI enhancements (custom) -->
    <link href="{{ asset('/css/mainheader-enhancements.css') }}" rel="stylesheet" type="text/css" />    
    <!-- Custom UI/UX Enhancements -->
    <link href="{{ asset('/css/color-palette.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/custom-tables.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/loading-states.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/dashboard-widgets.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/button-standards.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/search-enhancement.css') }}" rel="stylesheet" type="text/css" />
    <!-- Centralized UI Enhancements (Forms, Tables, Filters) -->
    <link href="{{ asset('/css/ui-enhancements.css') }}" rel="stylesheet" type="text/css" />
    <!-- Modern UI/UX Enhancements V2 -->
    <link href="{{ asset('/css/ui-enhancements-v2.css') }}" rel="stylesheet" type="text/css" />
    <!-- Enterprise Design System -->
    <link href="{{ asset('/css/enterprise-design-system.css') }}" rel="stylesheet" type="text/css" />
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap.min.css">
    <script src="{{ asset('/plugins/jQuery/jQuery-2.1.4.min.js') }}"></script>
    <script src="{{ asset('/js/search-enhancement.js') }}"></script>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
