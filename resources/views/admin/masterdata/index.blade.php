@extends('layouts.app')

@section('main-content')

@include('components.page-header', ['title' => 'Master Data Management', 'subtitle' => 'Import and export master data'])

<div class="row">
    <div class="col-md-6">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-upload"></i> Import Master Data</h3>
            </div>
            <div class="box-body">
                <p>Upload Excel file with multiple sheets to import all master data at once.</p>
                <p><strong>Supported Data Types:</strong></p>
                <div class="row">
                    <div class="col-md-6">
                        <ul style="line-height: 1.8;">
                            <li>Divisions</li>
                            <li>Locations</li>
                            <li>Users</li>
                            <li>Roles</li>
                            <li>Permissions</li>
                            <li>Asset Types</li>
                            <li>Manufacturers</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul style="line-height: 1.8;">
                            <li>Asset Models</li>
                            <li>Suppliers</li>
                            <li>Statuses</li>
                            <li>Warranty Types</li>
                            <li>Ticket Priorities</li>
                            <li>Ticket Statuses</li>
                            <li>Ticket Types</li>
                        </ul>
                    </div>
                </div>
                <hr>
                <a href="{{ route('masterdata.imports') }}" class="btn btn-success btn-lg">
                    <i class="fa fa-upload"></i> Go to Import Page
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-download"></i> Export Master Data</h3>
            </div>
            <div class="box-body">
                <p>Download all master data in Excel format with multiple sheets.</p>
                <p><strong>Export Includes:</strong></p>
                <ul style="line-height: 1.8;">
                    <li>All divisions, locations, and users</li>
                    <li>Roles and permissions</li>
                    <li>Asset types, models, manufacturers</li>
                    <li>Suppliers and statuses</li>
                    <li>Warranty types</li>
                    <li>Ticket configurations</li>
                </ul>
                <div class="callout callout-info">
                    <h4><i class="fa fa-info-circle"></i> Note</h4>
                    <p>User passwords are not exported for security reasons.</p>
                </div>
                <hr>
                <a href="{{ route('masterdata.export') }}" class="btn btn-primary btn-lg">
                    <i class="fa fa-download"></i> Export All Master Data
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-file-excel-o"></i> Templates & Feature-Specific Exports</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <h4>Import Templates</h4>
                        <ul>
                            <li><a href="{{ route('masterdata.template') }}"><i class="fa fa-download"></i> Master Data Template</a></li>
                            <li><a href="{{ route('masterdata.templates') }}"><i class="fa fa-list"></i> View All Templates</a></li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h4>Feature-Specific Exports</h4>
                        <ul>
                            @if(Route::has('assets.export'))
                                <li><a href="{{ route('assets.export') }}"><i class="fa fa-download"></i> Export Assets</a></li>
                            @endif
                            @if(Route::has('tickets.export'))
                                <li><a href="{{ route('tickets.export') }}"><i class="fa fa-download"></i> Export Tickets</a></li>
                            @endif
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h4>Import Results</h4>
                        <ul>
                            <li><a href="{{ route('masterdata.results') }}"><i class="fa fa-history"></i> View Import History</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
