@extends('layouts.app')

@section('main-content')
    <section class="content-header">
        <h1>
            Backup Details
            <small>{{ $backup['name'] }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('/home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li><a href="{{ route('admin.backup') }}">Backup</a></li>
            <li class="active">Details</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <!-- Backup Information -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-info-circle"></i> Backup Information
                        </h3>
                        <div class="box-tools pull-right">
                            <a href="{{ route('admin.backup') }}" class="btn btn-sm btn-default">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <dl class="dl-horizontal">
                            <dt>Backup Name:</dt>
                            <dd><strong>{{ $backup['name'] }}</strong></dd>

                            <dt>Created At:</dt>
                            <dd>{{ $backup['created_at'] }}</dd>

                            <dt>Total Size:</dt>
                            <dd><span class="label label-info">{{ $backup['size'] }}</span></dd>

                            <dt>Status:</dt>
                            <dd>
                                <span class="label label-success">
                                    <i class="fa fa-check-circle"></i> {{ ucfirst($backup['status']) }}
                                </span>
                            </dd>

                            <dt>Backup Types:</dt>
                            <dd>
                                @foreach($backup['types'] as $type)
                                    <span class="label label-primary" style="margin-right: 5px;">
                                        {{ ucfirst($type) }}
                                    </span>
                                @endforeach
                            </dd>
                        </dl>
                    </div>
                </div>

                <!-- Backup Files -->
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-files-o"></i> Backup Files ({{ count($backup['detailed_files']) }})
                        </h3>
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>File Name</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Modified</th>
                                    <th width="100">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($backup['detailed_files'] as $file)
                                    <tr>
                                        <td>
                                            <i class="fa fa-file-archive-o text-muted"></i> 
                                            {{ $file['name'] }}
                                        </td>
                                        <td>
                                            <span class="label label-default">{{ $file['type'] }}</span>
                                        </td>
                                        <td>{{ $file['size'] }}</td>
                                        <td>{{ $file['modified'] }}</td>
                                        <td>
                                            <a href="{{ route('admin.backup.download', $file['download_id']) }}" 
                                               class="btn btn-xs btn-success" 
                                               title="Download">
                                                <i class="fa fa-download"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            No files found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Actions -->
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-wrench"></i> Actions
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="btn-group btn-group-justified" role="group">
                            @if(count($backup['detailed_files']) === 1)
                                <a href="{{ route('admin.backup.download', $backup['id']) }}" 
                                   class="btn btn-success">
                                    <i class="fa fa-download"></i> Download Backup
                                </a>
                            @else
                                <a href="#" class="btn btn-success" onclick="alert('Download individual files from the table above'); return false;">
                                    <i class="fa fa-download"></i> Download Files
                                </a>
                            @endif
                            
                            <form method="POST" 
                                  action="{{ route('admin.backup.restore', $backup['id']) }}" 
                                  style="display: inline-block; width: 33%;"
                                  onsubmit="return confirm('⚠️ WARNING: This will restore the database from backup!\n\nAll current data will be replaced with backup data.\n\nAre you sure you want to continue?');">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-block">
                                    <i class="fa fa-refresh"></i> Restore Backup
                                </button>
                            </form>
                            
                            <form method="POST" 
                                  action="{{ route('admin.backup.delete', $backup['id']) }}" 
                                  style="display: inline-block; width: 33%;"
                                  onsubmit="return confirm('Are you sure you want to delete this backup?\n\nThis action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-block">
                                    <i class="fa fa-trash"></i> Delete Backup
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="box-footer">
                        <p class="text-muted text-sm">
                            <i class="fa fa-info-circle"></i> 
                            <strong>Note:</strong> Restore operation will replace current database with backup data. Make sure to create a fresh backup before restoring.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
