@extends('layouts.app')

@section('main-content')
    <section class="content-header">
        <h1>
            Backup Management
            <small>System backup and restore operations</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('/home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="active">Backup</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <!-- Backup Actions -->
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-download"></i> Create Backup
                        </h3>
                    </div>
                    <div class="box-body">
                        <form method="POST" action="{{ route('admin.backup.create') }}" id="backup-form">
                            @csrf
                            <div class="form-group">
                                <label>Backup Type:</label>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="backup_types[]" value="database" checked> 
                                        Database
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="backup_types[]" value="files" checked> 
                                        Application Files
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="backup_types[]" value="uploads"> 
                                        User Uploads
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="backup_types[]" value="config"> 
                                        Configuration Files
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="backup_name">Backup Name (Optional):</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="backup_name" 
                                       name="backup_name" 
                                       placeholder="e.g., before-update-{{ date('Y-m-d') }}">
                            </div>

                            <div class="form-group">
                                <label for="compression">Compression:</label>
                                <select class="form-control" id="compression" name="compression">
                                    <option value="gzip">Gzip (Recommended)</option>
                                    <option value="zip">ZIP</option>
                                    <option value="none">No Compression</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-download"></i> Create Backup
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Backup Status -->
            <div class="col-md-6">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-info-circle"></i> Backup Status
                        </h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-condensed">
                            <tr>
                                <td><strong>Last Backup:</strong></td>
                                <td>{{ $backup_status['last_backup'] ?? 'Never' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Backups:</strong></td>
                                <td>{{ $backup_status['total_backups'] ?? 0 }}</td>
                            </tr>
                            <tr>
                                <td><strong>Backup Directory:</strong></td>
                                <td><code>{{ $backup_status['backup_path'] ?? 'storage/backups' }}</code></td>
                            </tr>
                            <tr>
                                <td><strong>Total Size:</strong></td>
                                <td>{{ $backup_status['total_size'] ?? '0 MB' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Available Space:</strong></td>
                                <td>{{ $backup_status['available_space'] ?? 'Unknown' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Auto Backup:</strong></td>
                                <td>
                                    <span class="label label-{{ $backup_status['auto_backup'] ? 'success' : 'warning' }}">
                                        {{ $backup_status['auto_backup'] ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-cogs"></i> Backup Settings
                        </h3>
                    </div>
                    <div class="box-body">
                        <form method="POST" action="{{ route('admin.backup.settings') }}">
                            @csrf
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="auto_backup" value="1" 
                                           {{ $backup_settings['auto_backup'] ?? false ? 'checked' : '' }}> 
                                    Enable Automatic Backup
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="backup_frequency">Backup Frequency:</label>
                                <select class="form-control" name="backup_frequency">
                                    <option value="daily" {{ ($backup_settings['backup_frequency'] ?? '') === 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ ($backup_settings['backup_frequency'] ?? '') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ ($backup_settings['backup_frequency'] ?? '') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="retention_days">Keep Backups (Days):</label>
                                <input type="number" class="form-control" name="retention_days" 
                                       value="{{ $backup_settings['retention_days'] ?? 30 }}" min="1" max="365">
                            </div>
                            <button type="submit" class="btn btn-warning btn-block">
                                <i class="fa fa-save"></i> Save Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Existing Backups -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-archive"></i> Existing Backups
                        </h3>
                        <div class="box-tools pull-right">
                            <form method="POST" action="{{ route('admin.backup.cleanup') }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete old backups?')">
                                    <i class="fa fa-trash"></i> Cleanup Old Backups
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="box-body">
                        @if(isset($backups) && count($backups) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Backup Name</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Created</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($backups as $backup)
                                    <tr>
                                        <td><strong>{{ $backup['name'] }}</strong></td>
                                        <td>
                                            @foreach($backup['types'] as $type)
                                            <span class="label label-info">{{ $type }}</span>
                                            @endforeach
                                        </td>
                                        <td>{{ $backup['size'] }}</td>
                                        <td>{{ $backup['created_at'] }}</td>
                                        <td>
                                            <span class="label label-{{ $backup['status'] === 'complete' ? 'success' : 'warning' }}">
                                                {{ ucfirst($backup['status']) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.backup.download', $backup['id']) }}" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fa fa-download"></i> Download
                                                </a>
                                                <a href="{{ route('admin.backup.show', $backup['id']) }}" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fa fa-info"></i> Details
                                                </a>
                                                <form method="POST" 
                                                      action="{{ route('admin.backup.restore', $backup['id']) }}" 
                                                      style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" 
                                                            onclick="return confirmRestore('{{ $backup['name'] }}')">
                                                        <i class="fa fa-upload"></i> Restore
                                                    </button>
                                                </form>
                                                <form method="POST" 
                                                      action="{{ route('admin.backup.delete', $backup['id']) }}" 
                                                      style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Delete this backup?\n\nAll backup files will be permanently removed.')">
                                                        <i class="fa fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center text-muted">
                            <i class="fa fa-archive fa-3x"></i>
                            <p>No backups found. Create your first backup using the form above.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Restore Options -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-upload"></i> Manual Restore
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <form method="POST" action="{{ route('admin.backup.upload') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group">
                                        <label for="backup_file">Upload Backup File:</label>
                                        <input type="file" 
                                               class="form-control" 
                                               id="backup_file" 
                                               name="backup_file" 
                                               accept=".zip,.gz,.sql" 
                                               required>
                                        <p class="help-block">Accepted formats: SQL, SQL.GZ (compressed), ZIP</p>
                                    </div>
                                    <button type="submit" class="btn btn-warning" onclick="return confirm('⚠️ WARNING: This will restore the database!\n\nAll current data will be replaced.\n\nAre you sure?')">
                                        <i class="fa fa-upload"></i> Upload & Restore
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-danger">
                                    <h4><i class="fa fa-warning"></i> Warning!</h4>
                                    <p>Restoring a backup will:</p>
                                    <ul>
                                        <li>Overwrite current database data</li>
                                        <li>Replace application files</li>
                                        <li>Reset configuration settings</li>
                                        <li>This action cannot be undone</li>
                                    </ul>
                                    <p><strong>Always create a backup before restoring!</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function confirmRestore(backupName) {
    return confirm('WARNING: Restoring backup "' + backupName + '" will overwrite all current data. This cannot be undone. Are you sure you want to continue?');
}
</script>
@endsection
