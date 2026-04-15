@extends('layouts.app')

@section('title', 'Import Assets')

@section('main-content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-upload"></i> Import Assets</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('assets.download-template') }}" class="btn btn-success btn-sm">
                        <i class="fa fa-download"></i> Download Template
                    </a>
                    <a href="{{ route('assets.index') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to Assets
                    </a>
                </div>
            </div>
            
            <form action="{{ route('assets.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="box-body">
                    @if (session('import_summary'))
                        @php $summary = session('import_summary'); @endphp
                        <div class="alert alert-success">
                            <strong>{{ $summary['created'] ?? 0 }} assets imported.</strong>
                        </div>

                        @if (!empty($summary['errors']))
                            <div class="box box-danger">
                                <div class="box-header with-border">
                                    <h4 class="box-title">Import Errors</h4>
                                    <div class="box-tools pull-right">
                                        <a href="{{ route('assets.import-errors-download') }}" class="btn btn-sm btn-warning">
                                            <i class="fa fa-download"></i> Download Errors CSV
                                        </a>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <table class="table table-striped table-condensed">
                                        <thead>
                                            <tr>
                                                <th>Row</th>
                                                <th>Messages</th>
                                                <th>Data</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($summary['errors'] as $err)
                                            <tr>
                                                <td>{{ $err['row'] ?? '?' }}</td>
                                                <td>
                                                    @if(!empty($err['errors']))
                                                        <ul class="mb-0">
                                                            @foreach($err['errors'] as $m)
                                                                <li>{{ $m }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        {{ $err['error'] ?? 'Unknown error' }}
                                                    @endif
                                                </td>
                                                <td><pre style="white-space:pre-wrap">{{ json_encode($err['data'] ?? [], JSON_PRETTY_PRINT) }}</pre></td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <h4><i class="icon fa fa-ban"></i> Error!</h4>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="file">Choose Excel File</label>
                        <input type="file" 
                               class="form-control" 
                               id="file" 
                               name="file" 
                               accept=".xlsx,.xls,.csv"
                               required>
                        <small class="help-block">
                            Accepted formats: .xlsx, .xls, .csv (Max size: 2MB)
                        </small>
                    </div>

                    <div class="alert alert-info">
                        <h4><i class="icon fa fa-info"></i> Import Instructions:</h4>
                        <ol>
                            <li>Download the template file using the button above</li>
                            <li>Fill in your asset data following the template format</li>
                            <li>Make sure all required fields are filled</li>
                            <li>Asset tags must be unique</li>
                            <li>Dates should be in YYYY-MM-DD format</li>
                            <li>Upload the completed file using the form above</li>
                        </ol>
                    </div>
                </div>
                
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-upload"></i> Import Assets
                    </button>
                    <a href="{{ route('assets.index') }}" class="btn btn-default">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
