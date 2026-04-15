@extends('layouts.app')

@section('main-content')
  <div class="row">
    <div class="col-md-12 col-xs-12">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">Spares Management</h3>
          <div class="box-tools pull-right">
            @can('create', App\Asset::class)
              <a href="{{ route('spares.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Add Spare Part
              </a>
            @endcan
          </div>
        </div>
        <div class="box-body">
          @if(session('success'))
            <div class="alert alert-success alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <i class="icon fa fa-check"></i> {{ session('success') }}
            </div>
          @endif
          
          @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <i class="icon fa fa-ban"></i> {{ session('error') }}
            </div>
          @endif

          <table id="table" class="table table-striped table-bordered table-hover">
            <thead>
              <tr>
                <th>Asset Tag</th>
                <th>Name</th>
                <th>Model</th>
                <th>Asset Type</th>
                <th>Location</th>
                <th>Quantity</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($assets as $asset)
                <tr>
                  <td>{{ $asset->asset_tag }}</td>
                  <td>{{ $asset->name }}</td>
                  <td>{{ $asset->model->asset_model ?? 'N/A' }}</td>
                  <td>{{ $asset->model->asset_type->type_name ?? 'N/A' }}</td>
                  <td>{{ $asset->location->name ?? 'N/A' }}</td>
                  <td>{{ $asset->qty ?? 0 }}</td>
                  <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                    <div class="btn-group btn-group-xs" role="group">
                      <a href="{{ route('spares.show', $asset->id) }}" class="btn btn-xs btn-info" title="View">
                        <i class="fa fa-eye"></i>
                      </a>
                      @can('update', $asset)
                        <a href="{{ route('spares.edit', $asset->id) }}" class="btn btn-xs btn-warning" title="Edit">
                          <i class="fa fa-edit"></i>
                        </a>
                      @endcan
                      @can('delete', $asset)
                        <button type="button" class="btn btn-xs btn-danger" onclick="deleteSpare({{ $asset->id }})" title="Delete">
                          <i class="fa fa-trash"></i>
                        </button>
                      @endcan
                    </div>
                    @can('delete', $asset)
                      <form id="delete-spare-{{ $asset->id }}" action="{{ route('spares.destroy', $asset->id) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                      </form>
                    @endcan
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
          
          {{-- Laravel pagination removed - using DataTables pagination instead --}}
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
<script>
  function deleteSpare(id) {
    if (confirm('Are you sure you want to delete this spare part?')) {
      document.getElementById('delete-spare-' + id).submit();
    }
  }

  $(document).ready(function() {
    $('#table').DataTable({
      "paging": true,
      "pageLength": 20,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
      "language": {
        "emptyTable": "No spare parts found.",
        "zeroRecords": "No matching spare parts found."
      },
      "columnDefs": [
        { "orderable": false, "targets": 6 }
      ],
      "order": [[0, 'asc']]
    });
  });
</script>
@endsection


