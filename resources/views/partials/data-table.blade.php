{{-- Data Table Partial --}}
@php
    $tableId = $tableId ?? 'data-table';
    $tableClass = $tableClass ?? 'table table-bordered table-striped';
    $showSearch = $showSearch ?? true;
    $showExport = $showExport ?? true;
@endphp

<div class="table-responsive">
    <table id="{{ $tableId }}" class="{{ $tableClass }}">
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ $column['title'] }}</th>
                @endforeach
                @if(isset($actions) && $actions)
                    <th width="120">Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>

@push('scripts')
<script>
$(function () {
    $('#{{ $tableId }}').DataTable({
        'paging': true,
        'lengthChange': true,
        'searching': {{ $showSearch ? 'true' : 'false' }},
        'ordering': true,
        'info': true,
        'autoWidth': false,
        'responsive': true,
        @if($showExport)
        'buttons': [
            {
                extend: 'copy',
                className: 'btn btn-sm btn-primary'
            },
            {
                extend: 'csv',
                className: 'btn btn-sm btn-primary'
            },
            {
                extend: 'excel',
                className: 'btn btn-sm btn-primary'
            },
            {
                extend: 'pdf',
                className: 'btn btn-sm btn-primary'
            },
            {
                extend: 'print',
                className: 'btn btn-sm btn-primary'
            }
        ],
        'dom': 'Bfrtip'
        @endif
    });
});
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables/dataTables.bootstrap.css') }}">
@if($showExport)
<link rel="stylesheet" href="{{ asset('plugins/datatables/extensions/Buttons/css/buttons.bootstrap.min.css') }}">
@endif
@endpush