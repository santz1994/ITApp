{{-- 
    File Attachment Component
    
    Usage:
    @include('partials.file-uploader', [
        'model_type' => 'ticket',
        'model_id' => $ticket->id,
        'collection' => 'attachments',
        'max_files' => 10,
        'accept' => 'image/*,application/pdf,.doc,.docx'
    ])
--}}

@php
    $uploadId = 'uploader-' . uniqid();
    $collection = $collection ?? 'attachments';
    $max_files = $max_files ?? 10;
    $accept = $accept ?? '*';
@endphp

<div class="file-uploader-component" id="{{ $uploadId }}">
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-paperclip"></i> Attachments
            </h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('{{ $uploadId }}-input').click()">
                    <i class="fa fa-upload"></i> Upload Files
                </button>
            </div>
        </div>
        
        <div class="box-body">
            <!-- Hidden file input -->
            <input type="file" 
                   id="{{ $uploadId }}-input" 
                   multiple 
                   accept="{{ $accept }}"
                   style="display: none;" 
                   onchange="handleFileSelect(this, '{{ $uploadId }}', '{{ $model_type }}', {{ $model_id }}, '{{ $collection }}', {{ $max_files }})">
            
            <!-- Upload progress -->
            <div id="{{ $uploadId }}-progress" class="upload-progress" style="display: none;">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped active" role="progressbar" style="width: 0%">
                        <span class="sr-only">0% Complete</span>
                    </div>
                </div>
                <p class="text-muted upload-status">Uploading files...</p>
            </div>
            
            <!-- File list -->
            <div id="{{ $uploadId }}-list" class="attachments-list">
                <div class="text-center text-muted empty-state">
                    <i class="fa fa-cloud-upload fa-3x"></i>
                    <p>No attachments yet. Click "Upload Files" to add files.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.file-uploader-component {
    margin-bottom: 20px;
}

.attachments-list {
    min-height: 100px;
}

.attachment-item {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f9f9f9;
}

.attachment-info {
    display: flex;
    align-items: center;
    flex: 1;
}

.attachment-icon {
    font-size: 24px;
    margin-right: 15px;
    color: #3c8dbc;
}

.attachment-details {
    flex: 1;
}

.attachment-name {
    font-weight: 500;
    color: #333;
    margin-bottom: 2px;
}

.attachment-meta {
    font-size: 12px;
    color: #999;
}

.attachment-actions {
    display: flex;
    gap: 5px;
}

.upload-progress {
    margin-bottom: 20px;
}

.upload-status {
    margin-top: 10px;
    text-align: center;
}

.empty-state {
    padding: 40px 20px;
}

.empty-state i {
    color: #ddd;
    margin-bottom: 15px;
}
</style>

<script>
/**
 * Handle file selection
 */
function handleFileSelect(input, uploaderId, modelType, modelId, collection, maxFiles) {
    const files = Array.from(input.files);
    
    if (files.length === 0) {
        return;
    }
    
    // Check file count
    if (files.length > maxFiles) {
        alert(`You can only upload up to ${maxFiles} files at once.`);
        return;
    }
    
    // Show progress
    showProgress(uploaderId);
    
    // Upload files
    uploadFiles(files, uploaderId, modelType, modelId, collection);
    
    // Reset input
    input.value = '';
}

/**
 * Upload files to server
 */
function uploadFiles(files, uploaderId, modelType, modelId, collection) {
    const formData = new FormData();
    
    files.forEach((file, index) => {
        formData.append('files[]', file);
    });
    
    formData.append('model_type', modelType);
    formData.append('model_id', modelId);
    formData.append('collection', collection);
    formData.append('_token', '{{ csrf_token() }}');
    
    const progressBar = document.querySelector(`#${uploaderId}-progress .progress-bar`);
    const statusText = document.querySelector(`#${uploaderId}-progress .upload-status`);
    
    fetch('{{ route("attachments.bulk-upload") }}', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Success
            statusText.textContent = data.message;
            progressBar.style.width = '100%';
            
            // Reload file list
            setTimeout(() => {
                hideProgress(uploaderId);
                loadAttachments(uploaderId, modelType, modelId, collection);
            }, 1000);
            
            // Show success message
            toastr.success(data.message);
        } else {
            // Error
            statusText.textContent = 'Upload failed: ' + data.message;
            progressBar.classList.add('progress-bar-danger');
            toastr.error(data.message);
        }
    })
    .catch(error => {
        statusText.textContent = 'Upload failed';
        progressBar.classList.add('progress-bar-danger');
        toastr.error('Failed to upload files. Please try again.');
        console.error('Upload error:', error);
    });
}

/**
 * Load attachments from server
 */
function loadAttachments(uploaderId, modelType, modelId, collection) {
    const listContainer = document.getElementById(`${uploaderId}-list`);
    
    showLoading('Loading attachments...');
    
    fetch(`{{ route("attachments.index") }}?model_type=${modelType}&model_id=${modelId}&collection=${collection}`)
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success && data.data.length > 0) {
                renderAttachments(listContainer, data.data, uploaderId);
            } else {
                listContainer.innerHTML = `
                    <div class="text-center text-muted empty-state">
                        <i class="fa fa-cloud-upload fa-3x"></i>
                        <p>No attachments yet. Click "Upload Files" to add files.</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Load attachments error:', error);
            toastr.error('Failed to load attachments');
        });
}

/**
 * Render attachments in list
 */
function renderAttachments(container, attachments, uploaderId) {
    let html = '';
    
    attachments.forEach(attachment => {
        const icon = getFileIcon(attachment.mime_type);
        
        html += `
            <div class="attachment-item" data-id="${attachment.id}">
                <div class="attachment-info">
                    <div class="attachment-icon">
                        <i class="fa ${icon}"></i>
                    </div>
                    <div class="attachment-details">
                        <div class="attachment-name">${attachment.name}</div>
                        <div class="attachment-meta">
                            ${attachment.size} • ${attachment.created_at}
                        </div>
                    </div>
                </div>
                <div class="attachment-actions">
                    <a href="{{ url('/') }}/attachments/${attachment.id}/download" 
                       class="btn btn-sm btn-info" 
                       data-toggle="tooltip" 
                       title="Download"
                       target="_blank">
                        <i class="fa fa-download"></i>
                    </a>
                    <button type="button" 
                            class="btn btn-sm btn-danger delete-confirm" 
                            data-item-name="attachment ${attachment.name}"
                            data-toggle="tooltip" 
                            title="Delete"
                            onclick="deleteAttachment(${attachment.id}, '${uploaderId}')">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
}

/**
 * Delete attachment
 */
function deleteAttachment(attachmentId, uploaderId) {
    if (!confirm('Are you sure you want to delete this attachment?')) {
        return;
    }
    
    showLoading('Deleting...');
    
    fetch(`{{ url('/') }}/attachments/${attachmentId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            // Remove item from DOM
            const item = document.querySelector(`[data-id="${attachmentId}"]`);
            if (item) {
                item.remove();
            }
            
            toastr.success(data.message);
            
            // Check if empty
            const listContainer = document.getElementById(`${uploaderId}-list`);
            if (listContainer.children.length === 0) {
                listContainer.innerHTML = `
                    <div class="text-center text-muted empty-state">
                        <i class="fa fa-cloud-upload fa-3x"></i>
                        <p>No attachments yet. Click "Upload Files" to add files.</p>
                    </div>
                `;
            }
        } else {
            toastr.error(data.message);
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Delete error:', error);
        toastr.error('Failed to delete attachment');
    });
}

/**
 * Get appropriate icon for file type
 */
function getFileIcon(mimeType) {
    if (mimeType.startsWith('image/')) {
        return 'fa-file-image-o';
    } else if (mimeType.includes('pdf')) {
        return 'fa-file-pdf-o';
    } else if (mimeType.includes('word') || mimeType.includes('document')) {
        return 'fa-file-word-o';
    } else if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) {
        return 'fa-file-excel-o';
    } else if (mimeType.includes('zip') || mimeType.includes('compressed')) {
        return 'fa-file-archive-o';
    } else {
        return 'fa-file-o';
    }
}

/**
 * Show upload progress
 */
function showProgress(uploaderId) {
    document.getElementById(`${uploaderId}-progress`).style.display = 'block';
}

/**
 * Hide upload progress
 */
function hideProgress(uploaderId) {
    document.getElementById(`${uploaderId}-progress`).style.display = 'none';
}

// Auto-load attachments on page load
document.addEventListener('DOMContentLoaded', function() {
    const uploaderId = '{{ $uploadId }}';
    const modelType = '{{ $model_type }}';
    const modelId = {{ $model_id }};
    const collection = '{{ $collection }}';
    
    loadAttachments(uploaderId, modelType, modelId, collection);
});
</script>
