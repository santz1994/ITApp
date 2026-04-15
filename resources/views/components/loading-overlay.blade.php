{{-- Loading Overlay Component --}}
{{-- Usage: @include('components.loading-overlay', ['id' => 'loading', 'message' => 'Loading...']) --}}

<div class="loading-overlay" id="{{ $id ?? 'loading-overlay' }}" style="display: none;">
    <div class="loading-spinner">
        <i class="fa fa-spinner fa-spin fa-3x"></i>
        <p>{{ $message ?? 'Loading...' }}</p>
    </div>
</div>

@push('scripts')
<script>
    // Global loading functions
    window.showLoading = function(message = 'Loading...', id = '{{ $id ?? 'loading-overlay' }}') {
        const overlay = document.getElementById(id);
        if (overlay) {
            overlay.querySelector('p').textContent = message;
            overlay.style.display = 'flex';
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }
    };
    
    window.hideLoading = function(id = '{{ $id ?? 'loading-overlay' }}') {
        const overlay = document.getElementById(id);
        if (overlay) {
            overlay.style.display = 'none';
            overlay.classList.remove('active');
            document.body.style.overflow = ''; // Restore scrolling
        }
    };
    
    // Ensure loading overlay is hidden on page load
    document.addEventListener('DOMContentLoaded', function() {
        hideLoading('{{ $id ?? 'loading-overlay' }}');
    });
</script>
@endpush
