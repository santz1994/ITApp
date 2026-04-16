@extends('layouts.app')

@section('main-content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><span data-i18n="asset_request.show.form.title_prefix">Asset Request</span> #{{ $assetRequest->id }}</h3>
                    <div class="card-tools">
                        <div class="btn-group btn-group-xs" role="group" aria-label="Asset Request Show Language Toggle" style="margin-right: 8px;">
                            <button type="button" class="btn btn-default" id="assetRequestShowLanguageEnglish" data-lang="en">EN</button>
                            <button type="button" class="btn btn-default" id="assetRequestShowLanguageIndonesian" data-lang="id">ID</button>
                        </div>
                        @if ($assetRequest->status === 'pending' && Auth::user() && $assetRequest->requested_by === Auth::id())
                            <a href="{{ route('asset-requests.edit', $assetRequest->id) }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-edit"></i> <span data-i18n="asset_request.show.action.edit">Edit</span>
                            </a>
                        @endif
                        @if (Route::has('asset-requests.index'))
                            <a href="{{ route('asset-requests.index') }}" class="btn btn-sm btn-secondary"><span data-i18n="asset_request.show.action.back">Back to requests</span></a>
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
                            <h4 class="mb-3" data-i18n="asset_request.show.section.details">Request Details</h4>
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
                            <h4 class="mb-3" data-i18n="asset_request.show.section.justification">Justification</h4>
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
                                    <i class="fa fa-cogs"></i> <span data-i18n="asset_request.show.section.admin_actions">Admin Actions</span>
                                </h4>
                                @if($assetRequest->status === 'pending')
                                    <div class="btn-group-vertical w-100" role="group">
                                        <button class="btn btn-success text-left" data-toggle="modal" data-target="#approveModal">
                                            <i class="fa fa-check"></i> <span data-i18n="asset_request.show.action.approve">Approve Request</span>
                                        </button>
                                        <button class="btn btn-danger text-left" data-toggle="modal" data-target="#rejectModal">
                                            <i class="fa fa-times"></i> <span data-i18n="asset_request.show.action.reject">Reject Request</span>
                                        </button>
                                    </div>
                                @elseif($assetRequest->status === 'approved')
                                    <button class="btn btn-primary w-100" data-toggle="modal" data-target="#fulfillModal">
                                        <i class="fa fa-check-circle"></i> <span data-i18n="asset_request.show.action.fulfill">Mark as Fulfilled</span>
                                    </button>
                                @else
                                    <div class="alert alert-info">
                                        <small><span data-i18n="asset_request.show.status.no_actions">No admin actions available for this status</span>: {{ $assetRequest->status }}</small>
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
                    <h5 class="modal-title text-white" data-i18n="asset_request.show.modal.approve.title">Approve Request</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{ route('asset-requests.approve', $assetRequest->id) }}" method="POST" onsubmit="return window.assetRequestShowConfirm('asset_request.show.runtime.confirm.approve', 'Approve this request?')">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="admin_notes" data-i18n="asset_request.show.modal.approve.notes_label">Approval Notes (Optional)</label>
                            <textarea class="form-control" id="admin_notes" name="admin_notes" rows="4"></textarea>
                            <small class="text-muted" data-i18n="asset_request.show.modal.approve.notes_help">Add any notes about this approval</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-i18n="asset_request.show.action.cancel">Cancel</button>
                        <button type="submit" class="btn btn-success" data-i18n="asset_request.show.action.approve">Approve</button>
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
                    <h5 class="modal-title text-white" data-i18n="asset_request.show.modal.reject.title">Reject Request</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{ route('asset-requests.reject', $assetRequest->id) }}" method="POST" onsubmit="return window.assetRequestShowConfirm('asset_request.show.runtime.confirm.reject', 'Reject this request?')">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="reject_notes"><span data-i18n="asset_request.show.modal.reject.reason_label">Reason for Rejection</span> <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="reject_notes" name="admin_notes" rows="4" required></textarea>
                            <small class="text-muted" data-i18n="asset_request.show.modal.reject.reason_help">Please explain why this request is being rejected</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-i18n="asset_request.show.action.cancel">Cancel</button>
                        <button type="submit" class="btn btn-danger" data-i18n="asset_request.show.action.reject">Reject</button>
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
                    <h5 class="modal-title text-white" data-i18n="asset_request.show.modal.fulfill.title">Mark as Fulfilled</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{ route('asset-requests.fulfill', $assetRequest->id) }}" method="POST" onsubmit="return window.assetRequestShowConfirm('asset_request.show.runtime.confirm.fulfill', 'Mark this request as fulfilled?')">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="fulfillment_notes" data-i18n="asset_request.show.modal.fulfill.notes_label">Fulfillment Notes (Optional)</label>
                            <textarea class="form-control" id="fulfillment_notes" name="fulfillment_notes" rows="4"></textarea>
                            <small class="text-muted" data-i18n="asset_request.show.modal.fulfill.notes_help">Add notes about how this request was fulfilled (e.g., asset tag)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-i18n="asset_request.show.action.cancel">Cancel</button>
                        <button type="submit" class="btn btn-primary" data-i18n="asset_request.show.action.fulfill">Mark as Fulfilled</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('scripts')
<script>
(function() {
    var translations = {
        en: {
            'asset_request.show.form.title_prefix': 'Asset Request',
            'asset_request.show.action.edit': 'Edit',
            'asset_request.show.action.back': 'Back to requests',
            'asset_request.show.section.details': 'Request Details',
            'asset_request.show.section.justification': 'Justification',
            'asset_request.show.section.admin_actions': 'Admin Actions',
            'asset_request.show.action.approve': 'Approve Request',
            'asset_request.show.action.reject': 'Reject Request',
            'asset_request.show.action.fulfill': 'Mark as Fulfilled',
            'asset_request.show.action.cancel': 'Cancel',
            'asset_request.show.status.no_actions': 'No admin actions available for this status',
            'asset_request.show.modal.approve.title': 'Approve Request',
            'asset_request.show.modal.approve.notes_label': 'Approval Notes (Optional)',
            'asset_request.show.modal.approve.notes_help': 'Add any notes about this approval',
            'asset_request.show.modal.reject.title': 'Reject Request',
            'asset_request.show.modal.reject.reason_label': 'Reason for Rejection',
            'asset_request.show.modal.reject.reason_help': 'Please explain why this request is being rejected',
            'asset_request.show.modal.fulfill.title': 'Mark as Fulfilled',
            'asset_request.show.modal.fulfill.notes_label': 'Fulfillment Notes (Optional)',
            'asset_request.show.modal.fulfill.notes_help': 'Add notes about how this request was fulfilled (e.g., asset tag)',
            'asset_request.show.runtime.confirm.approve': 'Approve this request?',
            'asset_request.show.runtime.confirm.reject': 'Reject this request?',
            'asset_request.show.runtime.confirm.fulfill': 'Mark this request as fulfilled?'
        },
        id: {
            'asset_request.show.form.title_prefix': 'Permintaan Aset',
            'asset_request.show.action.edit': 'Ubah',
            'asset_request.show.action.back': 'Kembali ke daftar permintaan',
            'asset_request.show.section.details': 'Detail Permintaan',
            'asset_request.show.section.justification': 'Justifikasi',
            'asset_request.show.section.admin_actions': 'Aksi Admin',
            'asset_request.show.action.approve': 'Setujui Permintaan',
            'asset_request.show.action.reject': 'Tolak Permintaan',
            'asset_request.show.action.fulfill': 'Tandai Sudah Dipenuhi',
            'asset_request.show.action.cancel': 'Batal',
            'asset_request.show.status.no_actions': 'Tidak ada aksi admin untuk status ini',
            'asset_request.show.modal.approve.title': 'Setujui Permintaan',
            'asset_request.show.modal.approve.notes_label': 'Catatan Persetujuan (Opsional)',
            'asset_request.show.modal.approve.notes_help': 'Tambahkan catatan untuk persetujuan ini',
            'asset_request.show.modal.reject.title': 'Tolak Permintaan',
            'asset_request.show.modal.reject.reason_label': 'Alasan Penolakan',
            'asset_request.show.modal.reject.reason_help': 'Jelaskan alasan permintaan ini ditolak',
            'asset_request.show.modal.fulfill.title': 'Tandai Sudah Dipenuhi',
            'asset_request.show.modal.fulfill.notes_label': 'Catatan Pemenuhan (Opsional)',
            'asset_request.show.modal.fulfill.notes_help': 'Tambahkan catatan cara permintaan ini dipenuhi (mis. tag aset)',
            'asset_request.show.runtime.confirm.approve': 'Setujui permintaan ini?',
            'asset_request.show.runtime.confirm.reject': 'Tolak permintaan ini?',
            'asset_request.show.runtime.confirm.fulfill': 'Tandai permintaan ini sebagai sudah dipenuhi?'
        }
    };

    var currentLanguage = 'en';
    var userId = '{{ (int) auth()->id() }}';
    var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
    var englishButton = document.getElementById('assetRequestShowLanguageEnglish');
    var indonesianButton = document.getElementById('assetRequestShowLanguageIndonesian');

    function getLanguage() {
        try {
            var raw = window.localStorage.getItem(languageStorageKey);
            if (!raw) {
                return 'en';
            }

            var parsed = JSON.parse(raw);
            return parsed && parsed.language === 'id' ? 'id' : 'en';
        } catch (error) {
            return 'en';
        }
    }

    function saveLanguage(language) {
        try {
            var raw = window.localStorage.getItem(languageStorageKey);
            var parsed = raw ? JSON.parse(raw) : {};
            parsed.language = language === 'id' ? 'id' : 'en';
            window.localStorage.setItem(languageStorageKey, JSON.stringify(parsed));
        } catch (error) {
            // Keep silent if localStorage is unavailable.
        }
    }

    function getLabel(key, fallback) {
        var dictionary = translations[currentLanguage] || translations.en;
        return dictionary[key] || fallback || key;
    }

    function applyLanguage(language) {
        currentLanguage = language === 'id' ? 'id' : 'en';
        var dictionary = translations[currentLanguage] || translations.en;

        Array.prototype.forEach.call(document.querySelectorAll('[data-i18n]'), function(node) {
            var key = node.getAttribute('data-i18n');
            if (dictionary[key]) {
                node.textContent = dictionary[key];
            }
        });

        if (englishButton && indonesianButton) {
            englishButton.classList.toggle('active', currentLanguage === 'en');
            indonesianButton.classList.toggle('active', currentLanguage === 'id');
        }
    }

    function assetRequestShowConfirm(key, fallback) {
        return window.confirm(getLabel(key, fallback));
    }

    window.assetRequestShowLabel = getLabel;
    window.assetRequestShowConfirm = assetRequestShowConfirm;

    if (englishButton && indonesianButton) {
        englishButton.addEventListener('click', function() {
            saveLanguage('en');
            applyLanguage('en');
        });

        indonesianButton.addEventListener('click', function() {
            saveLanguage('id');
            applyLanguage('id');
        });
    }

    applyLanguage(getLanguage());
})();
</script>
@endpush
@endsection
