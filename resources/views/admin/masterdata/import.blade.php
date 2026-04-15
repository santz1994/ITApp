@extends('layouts.app')

@section('main-content')

@include('components.page-header', ['title' => 'Master Data Import', 'subtitle' => 'Upload Excel file with multiple sheets for comprehensive data import'])

<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Upload Master Data</h3>
            </div>
            <div class="box-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-check"></i> Success!</h4>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-ban"></i> Error!</h4>
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('results'))
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">Import Results</h3>
                        </div>
                        <div class="box-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Sheet Name</th>
                                        <th>Imported</th>
                                        <th>Skipped</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(session('results') as $sheet => $result)
                                        <tr>
                                            <td><strong>{{ ucwords(str_replace('_', ' ', $sheet)) }}</strong></td>
                                            <td><span class="label label-success">{{ $result['imported'] }}</span></td>
                                            <td><span class="label label-warning">{{ $result['skipped'] }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if(session('errors'))
                    <div class="alert alert-warning">
                        <h4>Errors Encountered:</h4>
                        <ul>
                            @foreach(session('errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="callout callout-info">
                    <h4><i class="icon fa fa-download"></i> Download Template First</h4>
                    <p>Download the Excel template with all sheets, fill in your data, then upload it here.</p>
                    <a href="{{ route('masterdata.template') }}" class="btn btn-info">
                        <i class="fa fa-download"></i> Download Excel Template
                    </a>
                </div>

                <form action="{{ route('masterdata.import.post') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="file">Upload Excel File (.xlsx or .xls)</label>
                        <input type="file" name="file" id="file" class="form-control" accept=".xlsx,.xls" required>
                        @error('file')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa fa-upload"></i> Upload and Import
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Supported Sheets</h3>
            </div>
            <div class="box-body">
                <p>The Excel file can contain any or all of these sheets:</p>
                <ul style="line-height: 1.8;">
                    <li><strong>Divisions</strong> - Company divisions</li>
                    <li><strong>Locations</strong> - Office locations</li>
                    <li><strong>Users</strong> - System users</li>
                    <li><strong>Roles</strong> - User roles</li>
                    <li><strong>Permissions</strong> - System permissions</li>
                    <li><strong>Asset_Types</strong> - Asset categories</li>
                    <li><strong>Manufacturers</strong> - Equipment makers</li>
                    <li><strong>Asset_Models</strong> - Asset models</li>
                    <li><strong>Suppliers</strong> - Vendors</li>
                    <li><strong>Statuses</strong> - Asset statuses</li>
                    <li><strong>Warranty_Types</strong> - Warranty types</li>
                    <li><strong>Ticket_Priorities</strong> - Ticket priorities</li>
                    <li><strong>Ticket_Statuses</strong> - Ticket statuses</li>
                    <li><strong>Ticket_Types</strong> - Ticket categories</li>
                </ul>
            </div>
        </div>

        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Important Notes</h3>
            </div>
            <div class="box-body">
                <ul>
                    <li>Only <strong>xlsx</strong> and <strong>xls</strong> formats are supported</li>
                    <li>Sheet names must match exactly (case-insensitive)</li>
                    <li>Empty rows are automatically skipped</li>
                    <li>Existing records are updated, new ones are created</li>
                    <li>Default password for users is <code>123456</code> if not specified</li>
                    <li>All notifications enabled by default for new users</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection
