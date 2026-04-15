@extends('layouts.app')

@section('main-content')
  <div class="row">
    <div class="col-md-6">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">Edit Ticket Status</h3>
        </div>
        <div class="box-body">
          <form method="POST" action="/admin/ticket-statuses/{{$ticketsStatus->id}}">
            {{method_field('PATCH')}}
            {{csrf_field()}}
            <div class="form-group {{ hasErrorForClass($errors, 'status') }}">
              <label for="status">Status</label>
              <input type="text" name="status" class="form-control" value="{{$ticketsStatus->status}}">
              {{ hasErrorForField($errors, 'status') }}
            </div>

            <div class="form-group">
              <button type="submit" class="btn btn-primary"><b>Edit Ticket Status</b></button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">Ticket Statuses</h3>
        </div>
        <div class="box-body">
          <ul>
            @foreach($ticketsStatuses as $status)
              <li>{{$status->status}}</li>
            @endforeach
        </ul>
        </div>
      </div>
    </div>
  </div>
  @endsection
  @if(Session::has('status'))
    <div class="alert alert-success" style="margin-top:10px;">
      {{ Session::get('message') }}
    </div>
    <script>
      $(document).ready(function() {
        Command: toastr["{{Session::get('status')}}"]("{{Session::get('message')}}", "{{Session::get('title')}}");
      });
    </script>
  @endif


