@extends('layouts.app')

@section('page_title')
    {{ $pageTitle }}
@endsection

@section('page_description')
    Add a new record to {{ $tableName }} table
@endsection

@section('main-content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-plus"></i> Add New Record
                </h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('admin.database.table', $tableName) }}" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to Table
                    </a>
                </div>
            </div>
            
            <form method="POST" action="{{ route('admin.database.store', $tableName) }}">
                @csrf
                
                <div class="box-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @foreach($editableColumns as $column)
                    <div class="form-group {{ $errors->has($column->column_name) ? 'has-error' : '' }}">
                        <label for="{{ $column->column_name }}">
                            {{ ucwords(str_replace('_', ' ', $column->column_name)) }}
                            @if(!$column->nullable && $column->default === null)
                                <span class="text-danger">*</span>
                            @endif
                        </label>
                        
                        @php
                            $inputType = 'text';
                            $inputClass = 'form-control';
                            $placeholder = '';
                            
                            // Determine input type based on column type
                            if (strpos($column->type, 'int') !== false) {
                                $inputType = 'number';
                            } elseif (strpos($column->type, 'decimal') !== false || strpos($column->type, 'float') !== false) {
                                $inputType = 'number';
                                $placeholder = 'step="0.01"';
                            } elseif (strpos($column->type, 'date') !== false && strpos($column->type, 'time') === false) {
                                $inputType = 'date';
                            } elseif (strpos($column->type, 'datetime') !== false || strpos($column->type, 'timestamp') !== false) {
                                $inputType = 'datetime-local';
                            } elseif (strpos($column->type, 'time') !== false) {
                                $inputType = 'time';
                            } elseif (strpos($column->type, 'email') !== false) {
                                $inputType = 'email';
                            } elseif (strpos($column->type, 'text') !== false || strpos($column->type, 'longtext') !== false) {
                                $inputType = 'textarea';
                            } elseif (strpos($column->type, 'tinyint(1)') !== false) {
                                $inputType = 'checkbox';
                            } elseif (strpos($column->type, 'enum') !== false) {
                                $inputType = 'select';
                                // Extract enum values
                                preg_match('/enum\((.*)\)/', $column->type, $matches);
                                $enumValues = [];
                                if (isset($matches[1])) {
                                    $enumValues = explode(',', $matches[1]);
                                    $enumValues = array_map(function($val) {
                                        return trim($val, "'\"");
                                    }, $enumValues);
                                }
                            }
                        @endphp

                        @if($inputType === 'textarea')
                            <textarea name="{{ $column->column_name }}" id="{{ $column->column_name }}" 
                                      class="{{ $inputClass }}" rows="4"
                                      {{ !$column->nullable && $column->default === null ? 'required' : '' }}
                                      placeholder="Enter {{ strtolower(str_replace('_', ' ', $column->column_name)) }}">{{ old($column->column_name) }}</textarea>
                        @elseif($inputType === 'select')
                            <select name="{{ $column->column_name }}" id="{{ $column->column_name }}" 
                                    class="{{ $inputClass }}"
                                    {{ !$column->nullable && $column->default === null ? 'required' : '' }}>
                                @if($column->nullable || $column->default !== null)
                                    <option value="">-- Select --</option>
                                @endif
                                @foreach($enumValues as $enumValue)
                                    <option value="{{ $enumValue }}" {{ old($column->column_name) == $enumValue ? 'selected' : '' }}>
                                        {{ ucfirst($enumValue) }}
                                    </option>
                                @endforeach
                            </select>
                        @elseif($inputType === 'checkbox')
                            <div class="checkbox">
                                <label>
                                    <input type="hidden" name="{{ $column->column_name }}" value="0">
                                    <input type="checkbox" name="{{ $column->column_name }}" id="{{ $column->column_name }}" 
                                           value="1" {{ old($column->column_name) ? 'checked' : '' }}>
                                    Yes
                                </label>
                            </div>
                        @else
                            <input type="{{ $inputType }}" name="{{ $column->column_name }}" id="{{ $column->column_name }}" 
                                   class="{{ $inputClass }}" value="{{ old($column->column_name) }}"
                                   {{ !$column->nullable && $column->default === null ? 'required' : '' }}
                                   {{ $placeholder }}
                                   @if($inputType === 'number' && strpos($column->type, 'decimal') !== false)
                                       step="0.01"
                                   @endif
                                   placeholder="Enter {{ strtolower(str_replace('_', ' ', $column->column_name)) }}">
                        @endif

                        @if($errors->has($column->column_name))
                            <span class="help-block">{{ $errors->first($column->column_name) }}</span>
                        @endif

                        <small class="help-block">
                            Type: <code>{{ $column->type }}</code>
                            @if(!$column->nullable)
                                <span class="text-danger">(Required)</span>
                            @endif
                            @if($column->default !== null)
                                <span class="text-muted">(Default: {{ $column->default }})</span>
                            @endif
                        </small>
                    </div>
                    @endforeach
                </div>

                <div class="box-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Create Record
                            </button>
                            <a href="{{ route('admin.database.table', $tableName) }}" class="btn btn-default">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>
                        <div class="col-md-6 text-right">
                            <small class="text-muted">
                                <span class="text-danger">*</span> Required fields
                            </small>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection