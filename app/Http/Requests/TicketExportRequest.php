<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * TicketExportRequest
 * 
 * Validates parameters for exporting tickets data
 */
class TicketExportRequest extends FormRequest
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
            'columns.*' => 'required|string|in:id,ticket_code,subject,description,status_id,priority_id,type_id,assigned_to,created_by,is_open,is_resolved,created_at,due_date,updated_at',
            
            // Filters
            'filters' => 'nullable|array',
            'filters.status_id' => 'nullable|array',
            'filters.status_id.*' => 'integer|exists:tickets_statuses,id',
            'filters.priority_id' => 'nullable|integer|exists:tickets_priorities,id',
            'filters.type_id' => 'nullable|integer|exists:tickets_types,id',
            'filters.assigned_to' => 'nullable|integer|exists:users,id',
            'filters.is_open' => 'nullable|boolean',
            'filters.is_resolved' => 'nullable|boolean',
            'filters.date_from' => 'nullable|date',
            'filters.date_to' => 'nullable|date|after_or_equal:filters.date_from',
            
            // Sorting
            'sort_by' => 'nullable|string|in:id,ticket_code,subject,created_at,updated_at,status_id,priority_id',
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
            'filters.status_id.*.integer' => 'Each status ID must be an integer',
            'filters.status_id.*.exists' => 'One or more status IDs are invalid',
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
        
        // Convert status_id array properly
        if (isset($filters['status_id']) && is_array($filters['status_id'])) {
            $filters['status_id'] = array_filter($filters['status_id']);
        }
        
        return array_filter($filters, function ($value) {
            return $value !== null && $value !== '' && (is_array($value) ? !empty($value) : true);
        });
    }
}
