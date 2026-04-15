@extends('layouts.app')

@section('main-content')

@include('components.page-header', ['title' => 'Import Results', 'subtitle' => 'Recent import job results'])

<div class="row">
  <div class="col-md-12">
    <div class="box box-primary">
      <div class="box-header with-border"><h3 class="box-title">Results</h3></div>
      <div class="box-body">
        @if(empty($files))
          <p>No result files found.</p>
        @else
          <table class="table table-striped">
            <thead><tr><th>File</th><th>Modified</th><th>Action</th></tr></thead>
            <tbody>
              @foreach($files as $f)
                <tr>
                  <td>{{ basename($f) }}</td>
                  <td>{{ date('Y-m-d H:i:s', filemtime(storage_path('app/' . $f))) }}</td>
                  <td><a class="btn btn-sm btn-default" href="{{ route('masterdata.results.download', basename($f)) }}">Download</a></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>
    </div>
  </div>
</div>

@endsection
