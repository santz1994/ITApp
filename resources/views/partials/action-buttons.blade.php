{{-- Action Buttons Partial --}}
@php
    $size = $size ?? 'sm';
    $showView = $showView ?? true;
    $showEdit = $showEdit ?? true;
    $showDelete = $showDelete ?? true;
    $viewRoute = $viewRoute ?? '';
    $editRoute = $editRoute ?? '';
    $deleteRoute = $deleteRoute ?? '';
    $viewPermission = $viewPermission ?? '';
    $editPermission = $editPermission ?? '';
    $deletePermission = $deletePermission ?? '';
@endphp

<div class="btn-group">
    @if($showView && $viewRoute)
        @if(!$viewPermission || auth()->user()->can($viewPermission))
            <a href="{{ $viewRoute }}" class="btn btn-info btn-{{ $size }}" title="View">
                <i class="fa fa-eye"></i>
            </a>
        @endif
    @endif
    
    @if($showEdit && $editRoute)
        @if(!$editPermission || auth()->user()->can($editPermission))
            <a href="{{ $editRoute }}" class="btn btn-primary btn-{{ $size }}" title="Edit">
                <i class="fa fa-edit"></i>
            </a>
        @endif
    @endif
    
    @if($showDelete && $deleteRoute)
        @if(!$deletePermission || auth()->user()->can($deletePermission))
            <form method="POST" action="{{ $deleteRoute }}" style="display: inline-block;" class="delete-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-{{ $size }}" title="Delete" 
                        onclick="return confirm('Are you sure you want to delete this item?')">
                    <i class="fa fa-trash"></i>
                </button>
            </form>
        @endif
    @endif
    
    @if(isset($customActions))
        {{ $customActions }}
    @endif
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = this;
        
        swal({
            title: 'Are you sure?',
            text: 'This action cannot be undone!',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (result.value) {
                form.submit();
            }
        });
    });
});
</script>
@endpush