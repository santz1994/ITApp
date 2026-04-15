<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * AssetExportRequest
 * 
 * Validates parameters for exporting assets data
 */
class AssetExportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        // Check for export permission
        return auth()->check();
    }

    /**
     * Get the validation rules
     */
    public function rules(): array
    {
        return [
            'format' => 'required|in:csv,excel,json',
            'columns' => 'required|array|min:1|max:20',
            'columns.*' => 'required|string|in:id,name,asset_tag,serial_number,status_id,location_id,assigned_to,manufacturer_id,asset_type_id,purchase_date,warranty_expiry_date,created_at,updated_at',
            
            // Filters
            'filters' => 'nullable|array',
            'filters.status_id' => 'nullable|integer|exists:statuses,id',
            'filters.location_id' => 'nullable|integer|exists:locations,id',
            'filters.assigned_to' => 'nullable|integer|exists:users,id',
            'filters.manufacturer_id' => 'nullable|integer|exists:manufacturers,id',
            'filters.date_from' => 'nullable|date',
            'filters.date_to' => 'nullable|date|after_or_equal:filters.date_from',
            
            // Sorting
            'sort_by' => 'nullable|string|in:id,name,asset_tag,created_at,updated_at,status_id',
            'sort_order' => 'nullable|in:asc,desc',
            
            // Export options
            'async' => 'nullable|boolean',
            'email_notification' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validation errors
     */
    public function messages(): array
    {
        return [
            'format.required' => 'Export format is required',
            'format.in' => 'Export format must be csv, excel, or json',
            'columns.required' => 'At least one column must be selected',
            'columns.min' => 'At least one column must be selected',
            'columns.*.in' => 'One or more selected columns are not valid',
            'filters.date_from.date' => 'Date from must be a valid date',
            'filters.date_to.date' => 'Date to must be a valid date',
            'filters.date_to.after_or_equal' => 'Date to must be after or equal to date from',
        ];
    }

    /**
     * Get export parameters formatted for export builder
     */
    public function getExportParams(): array
    {
        return [
            'format' => $this->input('format'),
            'columns' => $this->input('columns', []),
            'filters' => $this->getFilters(),
            'sort' => [
                'by' => $this->input('sort_by'),
                'order' => $this->input('sort_order', 'asc'),
            ],
            'async' => $this->boolean('async', false),
            'email_notification' => $this->boolean('email_notification', true),
        ];
    }

    /**
     * Get only filled filters
     */
    private function getFilters(): array
    {
        $filters = $this->input('filters', []);
        
        return array_filter($filters, function ($value) {
            return $value !== null && $value !== '';
        });
    }
}
