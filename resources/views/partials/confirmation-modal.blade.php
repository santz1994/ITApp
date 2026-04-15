{{-- Confirmation Modal Partial --}}
@php
    $modalId = $modalId ?? 'confirmationModal';
    $title = $title ?? 'Confirm Action';
    $message = $message ?? 'Are you sure you want to perform this action?';
    $confirmText = $confirmText ?? 'Confirm';
    $cancelText = $cancelText ?? 'Cancel';
    $confirmClass = $confirmClass ?? 'btn-danger';
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">{{ $title }}</h4>
            </div>
            <div class="modal-body">
                <p>{{ $message }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ $cancelText }}</button>
                <button type="button" class="btn {{ $confirmClass }}" id="{{ $modalId }}Confirm">{{ $confirmText }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    var {{ $modalId }}Form = null;
    
    // Store form reference when modal is triggered
    $('[data-toggle="modal"][data-target="#{{ $modalId }}"]').on('click', function() {
        {{ $modalId }}Form = $(this).closest('form');
    });
    
    // Handle confirmation
    $('#{{ $modalId }}Confirm').on('click', function() {
        if ({{ $modalId }}Form) {
            {{ $modalId }}Form.submit();
        }
        $('#{{ $modalId }}').modal('hide');
    });
});
</script>
@endpush