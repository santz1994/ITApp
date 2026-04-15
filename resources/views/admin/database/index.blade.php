@extends('layouts.app')

@section('page_title')
    Database Management
@endsection

@section('page_description')
    Manage database tables and records with full CRUD operations
@endsection

@section('main-content')
<div class="row">
    <!-- Database Overview -->
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-database"></i> Database Overview
                </h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('admin.database.backup') }}" class="btn btn-success btn-sm">
                        <i class="fa fa-download"></i> Full Backup
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-aqua"><i class="fa fa-table"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Tables</span>
                                <span class="info-box-number">{{ $stats['total_tables'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-green"><i class="fa fa-hdd-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Database Size</span>
                                <span class="info-box-number">{{ number_format($stats['database_size'] / 1024 / 1024, 2) }} MB</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-yellow"><i class="fa fa-plug"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Connection</span>
                                <span class="info-box-number">{{ $stats['connection'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-red"><i class="fa fa-server"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Database</span>
                                <span class="info-box-number">{{ $stats['database_name'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables List -->
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-list"></i> Database Tables
                </h3>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table id="tablesTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Table Name</th>
                                <th>Rows</th>
                                <th>Data Size</th>
                                <th>Index Size</th>
                                <th>Total Size</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tables as $table)
                            @php
                                $stats = $tableStats[$table] ?? null;
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $table }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-blue">
                                        {{ $stats ? number_format($stats->row_count ?? 0) : 'N/A' }}
                                    </span>
                                </td>
                                <td>{{ $stats ? number_format(($stats->data_size ?? 0) / 1024, 2) . ' KB' : 'N/A' }}</td>
                                <td>{{ $stats ? number_format(($stats->index_size ?? 0) / 1024, 2) . ' KB' : 'N/A' }}</td>
                                <td>{{ $stats ? number_format(($stats->total_size ?? 0) / 1024, 2) . ' KB' : 'N/A' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.database.table', $table) }}" class="btn btn-info" title="View Table">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.database.create', $table) }}" class="btn btn-success" title="Add Record">
                                            <i class="fa fa-plus"></i>
                                        </a>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" title="Export">
                                                <i class="fa fa-download"></i> <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('admin.database.export', [$table, 'csv']) }}">Export CSV</a></li>
                                                <li><a href="{{ route('admin.database.export', [$table, 'sql']) }}">Export SQL</a></li>
                                            </ul>
                                        </div>
                                        <button type="button" class="btn btn-danger" onclick="confirmTruncate('{{ $table }}')" title="Truncate Table">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Truncate Confirmation Modal -->
<div class="modal fade" id="truncateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Confirm Truncate</h4>
            </div>
            <div class="modal-body">
                <p><strong>WARNING:</strong> This will permanently delete all data from the table <span id="tableNameDisplay"></span>.</p>
                <p>This action cannot be undone. Are you sure you want to continue?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <form id="truncateForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Yes, Truncate Table</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer')
<script>
$(document).ready(function() {
    $('#tablesTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "pageLength": 25,
        "order": [[ 0, "asc" ]]
    });
});

function confirmTruncate(tableName) {
    $('#tableNameDisplay').text(tableName);
    $('#truncateForm').attr('action', '/admin/database/' + tableName + '/truncate');
    $('#truncateModal').modal('show');
}
</script>
@endsection