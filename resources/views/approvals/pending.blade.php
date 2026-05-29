@extends('layouts.app')

@section('main-content')
<div class="container-fluid">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-check-double"></i> Pending Approvals</h3>
        </div>
        <div class="box-body">
            @if($approvals && $approvals->count() > 0)
                @foreach($approvals as $approval)
                    <div class="box box-widget" style="margin-bottom: 15px;">
                        <div class="box-header with-border">
                            <span class="label label-warning">{{ strtoupper(str_replace('_', ' ', $approval->status)) }}</span>
                            <span class="username" style="margin-left: 10px;">Approval #{{ $approval->id }}</span>
                        </div>
                        <div class="box-body">
                            <p><strong>Current Step:</strong> {{ $approval->current_step ?? 'N/A' }}</p>
                            <div class="progress" style="margin-bottom: 10px;">
                                <div class="progress-bar progress-bar-aqua" style="width: {{ $approval->progress ?? 0 }}%">{{ $approval->progress ?? 0 }}%</div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <form action="{{ route('approvals.approve', $approval->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Approve</button>
                            </form>
                            <form action="{{ route('approvals.reject', $approval->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <input type="text" name="comments" placeholder="Rejection reason..." class="form-control" style="width: 250px; display: inline-block;">
                                <button type="submit" class="btn btn-danger"><i class="fa fa-times"></i> Reject</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center" style="padding: 40px;">
                    <i class="fa fa-check-circle fa-3x text-green"></i>
                    <p class="text-muted" style="margin-top: 15px;">No pending approvals.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
