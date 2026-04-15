<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * TicketFilterRequest
 * 
 * Validates advanced filtering parameters for ticket queries.
 * Supports date ranges, multi-select filters, and complex ticket-specific filters.
 * 
 * @package App\Http\Requests
 */
class TicketFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Search parameters
            'q' => 'nullable|string|min:2|max:200',
            'search_type' => 'nullable|in:subject,description,code,all',

            // Date range filters
            'date_from' => 'nullable|date_format:Y-m-d|before_or_equal:date_to',
            'date_to' => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
            'date_column' => 'nullable|in:created_at,updated_at,resolved_at,due_date,closed_at',

            // Multi-select filters (status)
            'status_id' => 'nullable|array',
            'status_id.*' => 'integer|exists:tickets_statuses,id',

            // Multi-select filters (priority)
            'priority_id' => 'nullable|array',
            'priority_id.*' => 'integer|exists:tickets_priority,id',

            // Multi-select filters (type)
            'type_id' => 'nullable|array',
            'type_id.*' => 'integer|exists:tickets_type,id',

            // Assigned to filter (single or multiple)
            'assigned_to' => 'nullable|array',
            'assigned_to.*' => 'integer|exists:users,id',

            // Created by filter
            'created_by' => 'nullable|integer|exists:users,id',

            // Location filter
            'location_id' => 'nullable|integer|exists:locations,id',
            'include_sublocation' => 'nullable|boolean',

            // Department/Division filter
            'department_id' => 'nullable|array',
            'department_id.*' => 'integer|exists:divisions,id',

            // Resolution status filter
            'is_resolved' => 'nullable|boolean',
            'is_open' => 'nullable|boolean',

            // Due date filter
            'due_from' => 'nullable|date_format:Y-m-d|before_or_equal:due_to',
            'due_to' => 'nullable|date_format:Y-m-d|after_or_equal:due_from',

            // Sorting
            'sort_by' => 'nullable|in:id,ticket_code,subject,status,priority,assigned_to,created_at,updated_at,due_date,relevance',
            'sort_order' => 'nullable|in:asc,desc',

            // Pagination
            'per_page' => 'nullable|integer|between:1,50',
            'page' => 'nullable|integer|min:1',

            // Include relationships
            'include' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'date_from.date_format' => 'Date from must be in format YYYY-MM-DD.',
            'date_to.date_format' => 'Date to must be in format YYYY-MM-DD.',
            'date_from.before_or_equal' => 'Date from must be before or equal to date to.',
            'date_to.after_or_equal' => 'Date to must be after or equal to date from.',
            'due_from.date_format' => 'Due from must be in format YYYY-MM-DD.',
            'due_to.date_format' => 'Due to must be in format YYYY-MM-DD.',
            'due_from.before_or_equal' => 'Due from must be before or equal to due to.',
            'due_to.after_or_equal' => 'Due to must be after or equal to due from.',
            'status_id.*.exists' => 'One or more selected statuses do not exist.',
            'priority_id.*.exists' => 'One or more selected priorities do not exist.',
            'type_id.*.exists' => 'One or more selected ticket types do not exist.',
            'assigned_to.*.exists' => 'One or more selected users do not exist.',
            'created_by.exists' => 'Selected user does not exist.',
            'location_id.exists' => 'Selected location does not exist.',
            'department_id.*.exists' => 'One or more selected departments do not exist.',
            'per_page.between' => 'Items per page must be between 1 and 50.',
        ];
    }

    /**
     * Get the parameters after validation.
     * 
     * Filters out empty values and provides defaults.
     */
    public function getFilterParams(): array
    {
        $validated = $this->validated();

        // Remove empty filters
        $filters = array_filter($validated, function ($value) {
            return $value !== null && $value !== '' && (is_array($value) ? count($value) > 0 : true);
        });

        // Set defaults
        $filters['per_page'] = min($filters['per_page'] ?? 15, 50);
        $filters['page'] = $filters['page'] ?? 1;
        $filters['sort_by'] = $filters['sort_by'] ?? 'id';
        $filters['sort_order'] = $filters['sort_order'] ?? 'desc';

        return $filters;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert string 'true'/'false' to boolean
        if ($this->has('include_sublocation')) {
            $this->merge([
                'include_sublocation' => filter_var($this->include_sublocation, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('is_resolved')) {
            $this->merge([
                'is_resolved' => filter_var($this->is_resolved, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('is_open')) {
            $this->merge([
                'is_open' => filter_var($this->is_open, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        // Ensure per_page is never more than 50
        if ($this->has('per_page')) {
            $this->merge([
                'per_page' => min((int)$this->per_page, 50),
            ]);
        }
    }
}
