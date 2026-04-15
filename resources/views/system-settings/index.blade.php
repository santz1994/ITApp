@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'System Settings',
    'subtitle' => 'Manage system configurations and master data',
    'icon' => 'fa-cogs',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'System Settings']
    ]
])

<div class="container-fluid">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-cogs"></i> Configuration Categories
                    </h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <!-- Ticket Configuration Section -->
                        <div class="col-md-6">
                            <div class="box box-info">
                                <div class="box-header with-border">
                                    <h3 class="box-title">
                                        <i class="fa fa-ticket-alt"></i> Ticket Configuration
                                    </h3>
                                </div>
                                <div class="box-body">
                                    <p style="margin-bottom: 15px; color: #666;">
                                        <i class="fa fa-info-circle"></i> Manage ticket types, statuses, priorities, and templates
                                    </p>
                                    <div class="list-group">
                                        <a href="{{ route('system-settings.canned-fields') }}" class="list-group-item" style="border-left: 3px solid #3c8dbc;">
                                            <i class="fa fa-text-width" style="color: #3c8dbc; width: 20px;"></i> Canned Fields
                                            <span class="badge bg-aqua pull-right">{{ \App\TicketsCannedField::count() }}</span>
                                        </a>
                                        <a href="{{ route('system-settings.ticket-statuses') }}" class="list-group-item" style="border-left: 3px solid #00a65a;">
                                            <i class="fa fa-flag" style="color: #00a65a; width: 20px;"></i> Ticket Statuses
                                            <span class="badge bg-green pull-right">{{ \App\TicketsStatus::count() }}</span>
                                        </a>
                                        <a href="{{ route('system-settings.ticket-types') }}" class="list-group-item" style="border-left: 3px solid #f39c12;">
                                            <i class="fa fa-tags" style="color: #f39c12; width: 20px;"></i> Ticket Types
                                            <span class="badge bg-orange pull-right">{{ \App\TicketsType::count() }}</span>
                                        </a>
                                        <a href="{{ route('system-settings.ticket-priorities') }}" class="list-group-item" style="border-left: 3px solid #dd4b39;">
                                            <i class="fa fa-exclamation-triangle" style="color: #dd4b39; width: 20px;"></i> Ticket Priorities
                                            <span class="badge bg-red pull-right">{{ \App\TicketsPriority::count() }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Asset Configuration Section -->
                        <div class="col-md-6">
                            <div class="box box-success">
                                <div class="box-header with-border">
                                    <h3 class="box-title">
                                        <i class="fa fa-laptop"></i> Asset Configuration
                                    </h3>
                                </div>
                                <div class="box-body">
                                    <p style="margin-bottom: 15px; color: #666;">
                                        <i class="fa fa-info-circle"></i> Configure asset types, statuses, divisions, and suppliers
                                    </p>
                                    <div class="list-group">
                                        <a href="{{ route('system-settings.asset-statuses') }}" class="list-group-item" style="border-left: 3px solid #605ca8;">
                                            <i class="fa fa-info-circle" style="color: #605ca8; width: 20px;"></i> Asset Statuses
                                            <span class="badge bg-purple pull-right">{{ \App\Status::count() }}</span>
                                        </a>
                                        <a href="{{ route('system-settings.divisions') }}" class="list-group-item" style="border-left: 3px solid #3c8dbc;">
                                            <i class="fa fa-building" style="color: #3c8dbc; width: 20px;"></i> Divisions
                                            <span class="badge bg-aqua pull-right">{{ \App\Division::count() }}</span>
                                        </a>
                                        <a href="{{ route('system-settings.suppliers') }}" class="list-group-item" style="border-left: 3px solid #00a65a;">
                                            <i class="fa fa-truck" style="color: #00a65a; width: 20px;"></i> Suppliers
                                            <span class="badge bg-green pull-right">{{ \App\Supplier::count() }}</span>
                                        </a>
                                        <a href="{{ route('system-settings.invoices') }}" class="list-group-item" style="border-left: 3px solid #f39c12;">
                                            <i class="fa fa-file-invoice-dollar" style="color: #f39c12; width: 20px;"></i> Invoices
                                            <span class="badge bg-orange pull-right">{{ \App\Invoice::count() }}</span>
                                        </a>
                                        <a href="{{ route('system-settings.warranty-types') }}" class="list-group-item" style="border-left: 3px solid #00c0ef;">
                                            <i class="fa fa-shield-alt" style="color: #00c0ef; width: 20px;"></i> Warranty Types
                                            <span class="badge bg-light-blue pull-right">{{ \App\WarrantyType::count() }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Storeroom Configuration Section -->
                        <div class="col-md-6">
                            <div class="box box-warning">
                                <div class="box-header with-border">
                                    <h3 class="box-title">
                                        <i class="fa fa-boxes"></i> Storeroom Management
                                    </h3>
                                </div>
                                <div class="box-body">
                                    <p style="margin-bottom: 15px; color: #666;">
                                        <i class="fa fa-info-circle"></i> Manage spare parts and storeroom inventory
                                    </p>
                                    <div class="list-group">
                                        <a href="{{ route('system-settings.storeroom') }}" class="list-group-item" style="border-left: 3px solid #f39c12;">
                                            <i class="fa fa-cube" style="color: #f39c12; width: 20px;"></i> Storeroom Items
                                            <span class="badge bg-orange pull-right">Manage</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Information Section -->
                        <div class="col-md-6">
                            <div class="box box-info">
                                <div class="box-header with-border">
                                    <h3 class="box-title">
                                        <i class="fa fa-info-circle"></i> System Information
                                    </h3>
                                </div>
                                <div class="box-body">
                                    <p style="margin-bottom: 15px; color: #666;">
                                        <i class="fa fa-server"></i> Technical details and environment information
                                    </p>
                                    <table class="table table-bordered table-hover">
                                        <tbody>
                                            <tr>
                                                <td style="width: 50%; font-weight: 600;">
                                                    <i class="fa fa-tag text-primary"></i> Application Version
                                                </td>
                                                <td>
                                                    <span class="badge bg-blue">{{ config('app.version', '1.0.0') }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight: 600;">
                                                    <i class="fa fa-code text-danger"></i> Laravel Version
                                                </td>
                                                <td>
                                                    <span class="badge bg-red">{{ app()->version() }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight: 600;">
                                                    <i class="fa fa-code-branch text-purple"></i> PHP Version
                                                </td>
                                                <td>
                                                    <span class="badge bg-purple">{{ PHP_VERSION }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight: 600;">
                                                    <i class="fa fa-server text-success"></i> Environment
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ app()->environment() === 'production' ? 'success' : 'warning' }}">
                                                        {{ strtoupper(app()->environment()) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight: 600;">
                                                    <i class="fa fa-database text-aqua"></i> Database Driver
                                                </td>
                                                <td>
                                                    <span class="badge bg-aqua">{{ config('database.default') }}</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Statistics --}}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title">
                                        <i class="fa fa-chart-bar"></i> System Overview
                                    </h3>
                                </div>
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-lg-3 col-xs-6">
                                            <div class="small-box bg-aqua">
                                                <div class="inner">
                                                    <h3>{{ \App\Asset::count() }}</h3>
                                                    <p>Total Assets</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fa fa-laptop"></i>
                                                </div>
                                                <a href="{{ url('/assets') }}" class="small-box-footer">
                                                    View Assets <i class="fa fa-arrow-circle-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-xs-6">
                                            <div class="small-box bg-green">
                                                <div class="inner">
                                                    <h3>{{ \App\Ticket::count() }}</h3>
                                                    <p>Total Tickets</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fa fa-ticket-alt"></i>
                                                </div>
                                                <a href="{{ url('/tickets') }}" class="small-box-footer">
                                                    View Tickets <i class="fa fa-arrow-circle-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-xs-6">
                                            <div class="small-box bg-yellow">
                                                <div class="inner">
                                                    <h3>{{ \App\User::count() }}</h3>
                                                    <p>System Users</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fa fa-users"></i>
                                                </div>
                                                <a href="{{ url('/admin/users') }}" class="small-box-footer">
                                                    Manage Users <i class="fa fa-arrow-circle-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-xs-6">
                                            <div class="small-box bg-red">
                                                <div class="inner">
                                                    <h3>{{ \App\Supplier::count() }}</h3>
                                                    <p>Active Suppliers</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fa fa-truck"></i>
                                                </div>
                                                <a href="{{ url('/suppliers') }}" class="small-box-footer">
                                                    View Suppliers <i class="fa fa-arrow-circle-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Enhanced hover effect for list group items
    $('.list-group-item').hover(
        function() {
            $(this).css({
                'background-color': '#f8f9fa',
                'transform': 'translateX(5px)',
                'transition': 'all 0.3s ease'
            });
        },
        function() {
            $(this).css({
                'background-color': '',
                'transform': 'translateX(0)',
                'transition': 'all 0.3s ease'
            });
        }
    );

    // Add click animation to small boxes
    $('.small-box').hover(
        function() {
            $(this).css({
                'transform': 'translateY(-5px)',
                'box-shadow': '0 10px 20px rgba(0,0,0,0.2)',
                'transition': 'all 0.3s ease'
            });
        },
        function() {
            $(this).css({
                'transform': 'translateY(0)',
                'box-shadow': '',
                'transition': 'all 0.3s ease'
            });
        }
    );

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Smooth scroll to section
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 1000);
        }
    });
});
</script>
@endpush