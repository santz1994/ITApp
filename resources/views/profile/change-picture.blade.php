@extends('layouts.app')

@section('title', 'Change Profile Picture')

@section('main-content')
<div class="row">
    {{-- Profile Sidebar --}}
    <div class="col-md-3">
        <div class="box box-primary">
            <div class="box-body box-profile">
                <img class="profile-user-img img-responsive img-circle" 
                     src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : asset('img/default-avatar.png') }}" 
                     alt="User profile picture">
                <h3 class="profile-username text-center">{{ $user->name }}</h3>
                <p class="text-muted text-center">{{ $user->email }}</p>

                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item">
                        <a href="{{ route('profile.edit') }}" class="{{ Request::is('profile') ? 'text-bold' : '' }}">
                            <i class="fa fa-user"></i> Edit Profile
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="{{ route('profile.edit-password') }}" class="{{ Request::is('profile/change-password') ? 'text-bold' : '' }}">
                            <i class="fa fa-lock"></i> Change Password
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="{{ route('profile.edit-picture') }}" class="{{ Request::is('profile/change-picture') ? 'text-bold' : '' }}">
                            <i class="fa fa-camera"></i> Change Picture
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="{{ route('profile.edit-notifications') }}" class="{{ Request::is('profile/notifications') ? 'text-bold' : '' }}">
                            <i class="fa fa-bell"></i> Notifications
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        {{-- Current Picture Box --}}
        <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-image"></i> Current Profile Picture</h3>
                </div>
                <div class="box-body text-center">
                    @if($user->profile_picture)
                        <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                             alt="Profile Picture" 
                             class="img-thumbnail current-profile-pic">
                        <p class="text-muted mt-2">
                            <small>Uploaded: {{ \Carbon\Carbon::parse(Storage::disk('public')->lastModified($user->profile_picture))->diffForHumans() }}</small>
                        </p>
                    @else
                        <img src="{{ asset('img/default-avatar.png') }}" 
                             alt="Default Avatar" 
                             class="img-thumbnail current-profile-pic">
                        <p class="text-muted mt-2">
                            <small><i class="fa fa-info-circle"></i> No profile picture uploaded yet</small>
                        </p>
                    @endif
                </div>
                
                @if($user->profile_picture)
                    <div class="box-footer text-center">
                        <form method="POST" action="{{ route('profile.delete-picture') }}" 
                              onsubmit="return confirm('Are you sure you want to delete your profile picture?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fa fa-trash"></i> Delete Picture
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            {{-- Upload New Picture Box --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-upload"></i> Upload New Picture</h3>
                </div>
                
                <form method="POST" action="{{ route('profile.update-picture') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="box-body">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> 
                            <strong>Picture Requirements:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Accepted formats: <strong>JPEG, JPG, PNG</strong></li>
                                <li>Maximum file size: <strong>2 MB</strong></li>
                                <li>Recommended size: <strong>200x200 pixels or larger</strong></li>
                                <li>Square images work best for profile pictures</li>
                            </ul>
                        </div>

                        <div class="form-group @error('profile_picture') has-error @enderror">
                            <label for="profile_picture">Select Image <span class="text-danger">*</span></label>
                            <input type="file" 
                                   class="form-control" 
                                   id="profile_picture" 
                                   name="profile_picture" 
                                   accept="image/jpeg,image/jpg,image/png"
                                   required
                                   onchange="previewImage(this)">
                            @error('profile_picture')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Image Preview --}}
                        <div id="preview-container" class="text-center" style="display: none;">
                            <h4>Preview:</h4>
                            <img id="preview-image" src="" alt="Preview" class="img-thumbnail preview-pic">
                            <p class="text-muted mt-2">
                                <small id="preview-info"></small>
                            </p>
                        </div>
                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-success" id="upload-btn" disabled>
                            <i class="fa fa-upload"></i> Upload Picture
                        </button>
                        <a href="{{ route('profile.edit') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back to Profile
                        </a>
                    </div>
                </form>
            </div>

            {{-- Tips Box --}}
            <div class="box box-default collapsed-box">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-lightbulb-o"></i> Tips for Best Results</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <ul>
                        <li><strong>Use a clear photo:</strong> Make sure your face is clearly visible</li>
                        <li><strong>Good lighting:</strong> Well-lit photos work best</li>
                        <li><strong>Square format:</strong> Crop to square before uploading for best results</li>
                        <li><strong>Professional appearance:</strong> Choose a photo appropriate for workplace</li>
                        <li><strong>Recent photo:</strong> Use a current photo that represents you</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function previewImage(input) {
    var uploadBtn = document.getElementById('upload-btn');
    var previewContainer = document.getElementById('preview-container');
    var previewImage = document.getElementById('preview-image');
    var previewInfo = document.getElementById('preview-info');
    
    if (input.files && input.files[0]) {
        var file = input.files[0];
        
        // Validate file size (2MB = 2048KB = 2097152 bytes)
        if (file.size > 2097152) {
            alert('File size is too large! Maximum size is 2 MB.');
            input.value = '';
            previewContainer.style.display = 'none';
            uploadBtn.disabled = true;
            return;
        }
        
        // Validate file type
        var validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            alert('Invalid file type! Please select a JPEG, JPG, or PNG image.');
            input.value = '';
            previewContainer.style.display = 'none';
            uploadBtn.disabled = true;
            return;
        }
        
        var reader = new FileReader();
        
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewContainer.style.display = 'block';
            
            // Format file size
            var sizeKB = (file.size / 1024).toFixed(2);
            var sizeMB = (file.size / 1048576).toFixed(2);
            var sizeText = sizeKB > 1024 ? sizeMB + ' MB' : sizeKB + ' KB';
            
            previewInfo.innerHTML = '<i class="fa fa-file-image-o"></i> <strong>' + file.name + '</strong> (' + sizeText + ')';
            uploadBtn.disabled = false;
            
            // Scroll to preview
            previewContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        };
        
        reader.readAsDataURL(file);
    } else {
        previewContainer.style.display = 'none';
        uploadBtn.disabled = true;
    }
}

// Drag and drop support
var fileInput = document.getElementById('profile_picture');
var box = fileInput.closest('.box');

box.addEventListener('dragover', function(e) {
    e.preventDefault();
    box.classList.add('drag-over');
});

box.addEventListener('dragleave', function(e) {
    e.preventDefault();
    box.classList.remove('drag-over');
});

box.addEventListener('drop', function(e) {
    e.preventDefault();
    box.classList.remove('drag-over');
    
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        previewImage(fileInput);
    }
});
</script>
@endpush

@push('styles')
<style>
    .current-profile-pic {
        max-width: 300px;
        max-height: 300px;
        margin: 20px auto;
        display: block;
        border-radius: 50%;
    }
    
    .preview-pic {
        max-width: 250px;
        max-height: 250px;
        margin: 15px auto;
        display: block;
    }
    
    .mt-2 {
        margin-top: 10px;
    }
    
    .mb-0 {
        margin-bottom: 0;
    }
    
    .drag-over {
        border: 2px dashed #3c8dbc;
        background-color: #f0f8ff;
    }
    
    #preview-container {
        margin-top: 20px;
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 4px;
    }
    
    .collapsed-box .box-body {
        display: none;
    }
</style>
@endpush
