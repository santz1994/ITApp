@extends('layouts.app')

@section('page_title')
    {{ $pageTitle }}
@endsection

@section('page_description')
    Edit record #{{ $id }} in {{ $tableName }} table
@endsection

@section('main-content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-edit"></i> Edit Record #{{ $id }}
                </h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('admin.database.table', $tableName) }}" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to Table
                    </a>
                </div>
            </div>
            
            <form method="POST" action="{{ route('admin.database.update', [$tableName, $id]) }}">
                @csrf
                @method('PUT')
                
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
                            @if(!$column->nullable && $column->default === null && $column->column_name !== 'updated_at')
                                <span class="text-danger">*</span>
                            @endif
                        </label>
                        
                        @php
                            $currentValue = $record->{$column->column_name} ?? '';
                            $oldValue = old($column->column_name, $currentValue);
                            
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
                                if ($oldValue && $oldValue !== '0000-00-00') {
                                    $oldValue = \Carbon\Carbon::parse($oldValue)->format('Y-m-d');
                                } else {
                                    $oldValue = '';
                                }
                            } elseif (strpos($column->type, 'datetime') !== false || strpos($column->type, 'timestamp') !== false) {
                                $inputType = 'datetime-local';
                                if ($oldValue && $oldValue !== '0000-00-00 00:00:00') {
                                    $oldValue = \Carbon\Carbon::parse($oldValue)->format('Y-m-d\TH:i');
                                } else {
                                    $oldValue = '';
                                }
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
                            
                            // Special handling for readonly fields
                            $isReadonly = in_array($column->column_name, ['id']) || $column->auto_increment;
                        @endphp

                        @if($isReadonly)
                            <input type="text" class="form-control" value="{{ $oldValue }}" readonly>
                            <input type="hidden" name="{{ $column->column_name }}" value="{{ $oldValue }}">
                        @elseif($inputType === 'textarea')
                            <textarea name="{{ $column->column_name }}" id="{{ $column->column_name }}" 
                                      class="{{ $inputClass }}" rows="4"
                                      {{ !$column->nullable && $column->default === null && $column->column_name !== 'updated_at' ? 'required' : '' }}
                                      placeholder="Enter {{ strtolower(str_replace('_', ' ', $column->column_name)) }}">{{ $oldValue }}</textarea>
                        @elseif($inputType === 'select')
                            <select name="{{ $column->column_name }}" id="{{ $column->column_name }}" 
                                    class="{{ $inputClass }}"
                                    {{ !$column->nullable && $column->default === null && $column->column_name !== 'updated_at' ? 'required' : '' }}>
                                @if($column->nullable || $column->default !== null)
                                    <option value="">-- Select --</option>
                                @endif
                                @foreach($enumValues as $enumValue)
                                    <option value="{{ $enumValue }}" {{ $oldValue == $enumValue ? 'selected' : '' }}>
                                        {{ ucfirst($enumValue) }}
                                    </option>
                                @endforeach
                            </select>
                        @elseif($inputType === 'checkbox')
                            <div class="checkbox">
                                <label>
                                    <input type="hidden" name="{{ $column->column_name }}" value="0">
                                    <input type="checkbox" name="{{ $column->column_name }}" id="{{ $column->column_name }}" 
                                           value="1" {{ $oldValue ? 'checked' : '' }}>
                                    Yes
                                </label>
                            </div>
                        @else
                            <input type="{{ $inputType }}" name="{{ $column->column_name }}" id="{{ $column->column_name }}" 
                                   class="{{ $inputClass }}" value="{{ $oldValue }}"
                                   {{ !$column->nullable && $column->default === null && $column->column_name !== 'updated_at' ? 'required' : '' }}
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
                            @if(!$column->nullable && $column->column_name !== 'updated_at')
                                <span class="text-danger">(Required)</span>
                            @endif
                            @if($column->default !== null)
                                <span class="text-muted">(Default: {{ $column->default }})</span>
                            @endif
                            @if($isReadonly)
                                <span class="text-info">(Read-only)</span>
                            @endif
                        </small>
                    </div>
                    @endforeach
                </div>

                <div class="box-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-warning">
                                <i class="fa fa-save"></i> Update Record
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