@extends('layouts.app')

@section('main-content')
<div class="container-fluid">
    <!-- Page Title -->
    <div class="row">
        <div class="col-md-12">
            <h1 class="page-header">
                <i class="fa fa-plus-circle"></i> Create New Menu
                <small>Add a new menu item to the system</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard.index') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="{{ route('admin.menus.index') }}"><i class="fa fa-bars"></i> Menu Management</a></li>
                <li class="active">Create Menu</li>
            </ol>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h4><i class="icon fa fa-ban"></i> Validation Errors!</h4>
        <ul>
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Create Form -->
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-edit"></i> Menu Details</h3>
                </div>
                <form action="{{ route('admin.menus.store') }}" method="POST" id="createMenuForm">
                    @csrf
                    <div class="box-body">
                        <!-- Label -->
                        <div class="form-group {{ $errors->has('label') ? 'has-error' : '' }}">
                            <label for="label">Menu Label <span class="text-red">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="label" 
                                   name="label" 
                                   placeholder="e.g., Dashboard, User Management" 
                                   value="{{ old('label') }}"
                                   required>
                            @if($errors->has('label'))
                            <span class="help-block">{{ $errors->first('label') }}</span>
                            @endif
                        </div>

                        <!-- Icon -->
                        <div class="form-group {{ $errors->has('icon') ? 'has-error' : '' }}">
                            <label for="icon">Icon (FontAwesome)</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa" id="icon-preview"></i></span>
                                <input type="text" 
                                       class="form-control" 
                                       id="icon" 
                                       name="icon" 
                                       placeholder="e.g., fa-dashboard, fa-users" 
                                       value="{{ old('icon') }}">
                            </div>
                            <span class="help-block">Enter FontAwesome class (e.g., fa-dashboard). See <a href="https://fontawesome.com/v4/icons/" target="_blank">icons list</a></span>
                            @if($errors->has('icon'))
                            <span class="help-block text-red">{{ $errors->first('icon') }}</span>
                            @endif
                        </div>

                        <!-- Route or URL -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('route') ? 'has-error' : '' }}">
                                    <label for="route">Route Name</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="route" 
                                           name="route" 
                                           placeholder="e.g., dashboard, users.index" 
                                           value="{{ old('route') }}">
                                    <span class="help-block">Laravel route name (check routes/web.php)</span>
                                    @if($errors->has('route'))
                                    <span class="help-block text-red">{{ $errors->first('route') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('url') ? 'has-error' : '' }}">
                                    <label for="url">URL (if external)</label>
                                    <input type="url" 
                                           class="form-control" 
                                           id="url" 
                                           name="url" 
                                           placeholder="e.g., https://example.com" 
                                           value="{{ old('url') }}">
                                    <span class="help-block">Full URL for external links</span>
                                    @if($errors->has('url'))
                                    <span class="help-block text-red">{{ $errors->first('url') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> Either <strong>Route Name</strong> or <strong>URL</strong> must be provided.
                        </div>

                        <!-- Parent Menu -->
                        <div class="form-group {{ $errors->has('parent_id') ? 'has-error' : '' }}">
                            <label for="parent_id">Parent Menu</label>
                            <select class="form-control select2" id="parent_id" name="parent_id">
                                <option value="">-- None (Top Level) --</option>
                                @foreach($parentMenus as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->label }}
                                </option>
                                @endforeach
                            </select>
                            <span class="help-block">Select parent menu if this is a submenu</span>
                            @if($errors->has('parent_id'))
                            <span class="help-block text-red">{{ $errors->first('parent_id') }}</span>
                            @endif
                        </div>

                        <!-- Order Index -->
                        <div class="form-group {{ $errors->has('order_index') ? 'has-error' : '' }}">
                            <label for="order_index">Display Order</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="order_index" 
                                   name="order_index" 
                                   placeholder="0" 
                                   value="{{ old('order_index', 0) }}"
                                   min="0">
                            <span class="help-block">Lower numbers appear first (0 = first)</span>
                            @if($errors->has('order_index'))
                            <span class="help-block text-red">{{ $errors->first('order_index') }}</span>
                            @endif
                        </div>

                        <!-- Description -->
                        <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                            <label for="description">Description (Optional)</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="3" 
                                      placeholder="Brief description of this menu item">{{ old('description') }}</textarea>
                            @if($errors->has('description'))
                            <span class="help-block text-red">{{ $errors->first('description') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Create Menu
                        </button>
                        <a href="{{ route('admin.menus.index') }}" class="btn btn-default">
                            <i class="fa fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Settings Sidebar -->
        <div class="col-md-4">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-cog"></i> Menu Settings</h3>
                </div>
                <div class="box-body">
                    <!-- Is Active -->
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <strong>Active</strong> - Menu will be visible to users
                            </label>
                        </div>
                    </div>

                    <!-- Is External -->
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="is_external" value="1" {{ old('is_external') ? 'checked' : '' }}>
                                <strong>External Link</strong> - Opens in new tab
                            </label>
                        </div>
                    </div>

                    <!-- Target -->
                    <div class="form-group">
                        <label>Link Target</label>
                        <select class="form-control" name="target" id="target">
                            <option value="_self" {{ old('target', '_self') == '_self' ? 'selected' : '' }}>Same Window (_self)</option>
                            <option value="_blank" {{ old('target') == '_blank' ? 'selected' : '' }}>New Tab (_blank)</option>
                            <option value="_parent" {{ old('target') == '_parent' ? 'selected' : '' }}>Parent Frame (_parent)</option>
                            <option value="_top" {{ old('target') == '_top' ? 'selected' : '' }}>Top Frame (_top)</option>
                        </select>
                    </div>

                    <!-- CSS Class -->
                    <div class="form-group">
                        <label for="css_class">Custom CSS Class</label>
                        <input type="text" 
                               class="form-control" 
                               id="css_class" 
                               name="css_class" 
                               placeholder="e.g., custom-menu-item" 
                               value="{{ old('css_class') }}">
                    </div>
                </div>
            </div>

            <!-- Help Box -->
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-question-circle"></i> Help</h3>
                </div>
                <div class="box-body">
                    <p><strong>Route vs URL:</strong></p>
                    <ul>
                        <li><strong>Route:</strong> Use for internal pages (e.g., "dashboard")</li>
                        <li><strong>URL:</strong> Use for external links (e.g., "https://google.com")</li>
                    </ul>
                    <p><strong>Parent Menu:</strong></p>
                    <ul>
                        <li>Leave empty for top-level menus</li>
                        <li>Select a parent to create submenus</li>
                    </ul>
                    <p><strong>Permissions:</strong></p>
                    <ul>
                        <li>After creating, go to "Permissions" to assign roles</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2();
    
    // Icon preview
    $('#icon').on('input', function() {
        var iconClass = $(this).val();
        $('#icon-preview').attr('class', 'fa ' + iconClass);
    });
    
    // Auto-fill target based on is_external checkbox
    $('input[name="is_external"]').on('change', function() {
        if ($(this).is(':checked')) {
            $('#target').val('_blank');
        } else {
            $('#target').val('_self');
        }
    });
    
    // Form validation
    $('#createMenuForm').on('submit', function(e) {
        var route = $('#route').val().trim();
        var url = $('#url').val().trim();
        
        if (!route && !url) {
            e.preventDefault();
            alert('Please provide either a Route Name or URL!');
            return false;
        }
    });
});
</script>
@endpush
@endsection
