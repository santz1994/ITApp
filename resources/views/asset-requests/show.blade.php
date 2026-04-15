@extends('layouts.app')

@section('main-content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Asset Request #{{ $assetRequest->id }}</h3>
                    <div class="card-tools">
                        @if ($assetRequest->status === 'pending' && Auth::user() && $assetRequest->requested_by === Auth::id())
                            <a href="{{ route('asset-requests.edit', $assetRequest->id) }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                        @endif
                        @if (Route::has('asset-requests.index'))
                            <a href="{{ route('asset-requests.index') }}" class="btn btn-sm btn-secondary">Back to requests</a>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="mb-3">Request Details</h4>
                            <dl class="row">
                                <dt class="col-sm-3">Request ID</dt>
                                <dd class="col-sm-9">#{{ $assetRequest->id }} @if($assetRequest->request_number) <small class="text-muted">({{ $assetRequest->request_number }})</small> @endif</dd>

                                <dt class="col-sm-3">Asset Type</dt>
                                <dd class="col-sm-9">
                                    @if($assetRequest->assetType)
                                        <span class="badge badge-info">{{ $assetRequest->assetType->type_name ?? $assetRequest->assetType->name ?? 'N/A' }}</span>
                                    @else
                                        <span class="text-muted">Not specified</span>
                                    @endif
                                </dd>

                                <dt class="col-sm-3">Requested By</dt>
                                <dd class="col-sm-9">
                                    @if($assetRequest->requestedBy)
                                        <strong>{{ $assetRequest->requestedBy->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $assetRequest->requestedBy->email }}</small>
                                    @else
                                        <span class="text-muted">Unknown user</span>
                                    @endif
                                </dd>

                                <dt class="col-sm-3">Status</dt>
                                <dd class="col-sm-9">
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'fulfilled' => 'primary'
                                        ];
                                        $statusColor = $statusColors[$assetRequest->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge badge-{{ $statusColor }}">{{ ucfirst($assetRequest->status) }}</span>
                                </dd>

                                <dt class="col-sm-3">Created</dt>
                                <dd class="col-sm-9">{{ \Illuminate\Support\Carbon::parse($assetRequest->created_at)->format('d M Y H:i') }}</dd>

                                <dt class="col-sm-3">Last Updated</dt>
                                <dd class="col-sm-9">{{ \Illuminate\Support\Carbon::parse($assetRequest->updated_at)->format('d M Y H:i') }}</dd>

                                @if($assetRequest->approved_by)
                                    <dt class="col-sm-3">Approved By</dt>
                                    <dd class="col-sm-9">
                                        <strong>{{ $assetRequest->approvedBy->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ \Illuminate\Support\Carbon::parse($assetRequest->approved_at)->format('d M Y H:i') }}</small>
                                    </dd>

                                    @if($assetRequest->approval_notes)
                                        <dt class="col-sm-3">Approval Notes</dt>
                                        <dd class="col-sm-9">
                                            <div class="alert alert-info mb-0">
                                                {{ $assetRequest->approval_notes }}
                                            </div>
                                        </dd>
                                    @endif
                                @endif

                                @if($assetRequest->fulfilled_asset_id)
                                    <dt class="col-sm-3">Fulfilled Asset</dt>
                                    <dd class="col-sm-9">
                                        <a href="{{ route('assets.show', $assetRequest->fulfilledAsset->id) }}" target="_blank">
                                            {{ $assetRequest->fulfilledAsset->asset_tag ?? 'Asset #' . $assetRequest->fulfilledAsset->id }}
                                        </a>
                                        <br>
                                        <small class="text-muted">{{ \Illuminate\Support\Carbon::parse($assetRequest->fulfilled_at)->format('d M Y H:i') }}</small>
                                    </dd>
                                @endif
                            </dl>
                        </div>

                        <div class="col-md-4">
                            <h4 class="mb-3">Justification</h4>
                            <div class="card border-left-primary">
                                <div class="card-body">
                                    @if($assetRequest->justification)
                                        <p>{{ $assetRequest->justification }}</p>
                                    @else
                                        <p class="text-muted"><em>No justification provided</em></p>
                                    @endif
                                </div>
                            </div>

                            @auth
                                <h4 class="mt-4 mb-3">
                                    <i class="fa fa-cogs"></i> Admin Actions
                                </h4>
                                @if($assetRequest->status === 'pending')
                                    <div class="btn-group-vertical w-100" role="group">
                                        <button class="btn btn-success text-left" data-toggle="modal" data-target="#approveModal">
                                            <i class="fa fa-check"></i> Approve Request
                                        </button>
                                        <button class="btn btn-danger text-left" data-toggle="modal" data-target="#rejectModal">
                                            <i class="fa fa-times"></i> Reject Request
                                        </button>
                                    </div>
                                @elseif($assetRequest->status === 'approved')
                                    <button class="btn btn-primary w-100" data-toggle="modal" data-target="#fulfillModal">
                                        <i class="fa fa-check-circle"></i> Mark as Fulfilled
                                    </button>
                                @else
                                    <div class="alert alert-info">
                                        <small>No admin actions available for {{ $assetRequest->status }} requests</small>
                                    </div>
                                @endif
                            @else
                                <div class="alert alert-warning">
                                    <small><a href="{{ route('login') }}">Please log in</a> to perform admin actions</small>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white">Approve Request</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{ route('asset-requests.approve', $assetRequest->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="admin_notes">Approval Notes (Optional)</label>
                            <textarea class="form-control" id="admin_notes" name="admin_notes" rows="4"></textarea>
                            <small class="text-muted">Add any notes about this approval</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">Reject Request</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{ route('asset-requests.reject', $assetRequest->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="reject_notes">Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="reject_notes" name="admin_notes" rows="4" required></textarea>
                            <small class="text-muted">Please explain why this request is being rejected</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Fulfill Modal -->
    <div class="modal fade" id="fulfillModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Mark as Fulfilled</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{ route('asset-requests.fulfill', $assetRequest->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="fulfillment_notes">Fulfillment Notes (Optional)</label>
                            <textarea class="form-control" id="fulfillment_notes" name="fulfillment_notes" rows="4"></textarea>
                            <small class="text-muted">Add notes about how this request was fulfilled (e.g., asset tag)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Mark as Fulfilled</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
