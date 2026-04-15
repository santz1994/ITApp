@extends('layouts.app')

@section('main-content')

@include('components.page-header', ['title' => 'Import Templates', 'subtitle' => 'Download templates for data import'])

<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Available Templates</h3>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Template Name</th>
                            <th style="width: 150px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $counter = 1; @endphp
                        @foreach($templates as $label => $link)
                            <tr>
                                <td>{{ $counter++ }}</td>
                                <td><strong>{{ $label }}</strong></td>
                                <td>
                                    <a href="{{ $link }}" class="btn btn-primary btn-sm">
                                        <i class="fa fa-download"></i> Download
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">About Templates</h3>
            </div>
            <div class="box-body">
                <h4>Master Data Template</h4>
                <p>The master data template contains 14 sheets for importing all types of master data:</p>
                <ul style="line-height: 1.6;">
                    <li>Divisions & Locations</li>
                    <li>Users with roles</li>
                    <li>Asset configurations</li>
                    <li>Ticket settings</li>
                    <li>And more...</li>
                </ul>
                <hr>
                <h4>How to Use</h4>
                <ol style="line-height: 1.6;">
                    <li>Download the template</li>
                    <li>Fill in your data</li>
                    <li>Upload via <a href="{{ route('masterdata.imports') }}">Import Page</a></li>
                </ol>
            </div>
        </div>
        
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Quick Links</h3>
            </div>
            <div class="box-body">
                <a href="{{ route('masterdata.imports') }}" class="btn btn-success btn-block">
                    <i class="fa fa-upload"></i> Go to Import Page
                </a>
                <a href="{{ route('masterdata.index') }}" class="btn btn-default btn-block">
                    <i class="fa fa-arrow-left"></i> Back to Overview
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
