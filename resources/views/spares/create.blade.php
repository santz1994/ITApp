@extends('layouts.app')

@section('main-content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Add New Spare Part</h3>
            </div>
            
            <form action="{{ route('spares.store') }}" method="POST">
                @csrf
                
                <div class="box-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group {{ $errors->has('asset_tag') ? 'has-error' : '' }}">
                        <label for="asset_tag">Asset Tag <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="asset_tag" 
                               name="asset_tag" 
                               value="{{ old('asset_tag') }}" 
                               required>
                        @if ($errors->has('asset_tag'))
                            <span class="help-block">{{ $errors->first('asset_tag') }}</span>
                        @endif
                    </div>

                    <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <label for="name">Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required>
                        @if ($errors->has('name'))
                            <span class="help-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>

                    <div class="form-group {{ $errors->has('model_id') ? 'has-error' : '' }}">
                        <label for="model_id">Model</label>
                        <select class="form-control select2" id="model_id" name="model_id">
                            <option value="">-- Select Model --</option>
                            @foreach($models as $model)
                                <option value="{{ $model->id }}" {{ old('model_id') == $model->id ? 'selected' : '' }}>
                                    {{ $model->asset_model }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('model_id'))
                            <span class="help-block">{{ $errors->first('model_id') }}</span>
                        @endif
                    </div>

                    <div class="form-group {{ $errors->has('asset_type_id') ? 'has-error' : '' }}">
                        <label for="asset_type_id">Asset Type <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="asset_type_id" name="asset_type_id" required>
                            <option value="">-- Select Asset Type --</option>
                            @foreach($assetTypes as $type)
                                <option value="{{ $type->id }}" {{ old('asset_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->type_name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('asset_type_id'))
                            <span class="help-block">{{ $errors->first('asset_type_id') }}</span>
                        @endif
                    </div>

                    <div class="form-group {{ $errors->has('location_id') ? 'has-error' : '' }}">
                        <label for="location_id">Location</label>
                        <select class="form-control select2" id="location_id" name="location_id">
                            <option value="">-- Select Location --</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('location_id'))
                            <span class="help-block">{{ $errors->first('location_id') }}</span>
                        @endif
                    </div>

                    <div class="form-group {{ $errors->has('qty') ? 'has-error' : '' }}">
                        <label for="qty">Quantity</label>
                        <input type="number" 
                               class="form-control" 
                               id="qty" 
                               name="qty" 
                               value="{{ old('qty', 0) }}" 
                               min="0">
                        @if ($errors->has('qty'))
                            <span class="help-block">{{ $errors->first('qty') }}</span>
                        @endif
                    </div>

                    <div class="form-group {{ $errors->has('notes') ? 'has-error' : '' }}">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" 
                                  id="notes" 
                                  name="notes" 
                                  rows="3">{{ old('notes') }}</textarea>
                        @if ($errors->has('notes'))
                            <span class="help-block">{{ $errors->first('notes') }}</span>
                        @endif
                    </div>
                </div>

                <div class="box-footer">
                    <a href="{{ route('spares.index') }}" class="btn btn-default">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary pull-right">
                        <i class="fa fa-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap',
        width: '100%'
    });
});
</script>
@endsection
