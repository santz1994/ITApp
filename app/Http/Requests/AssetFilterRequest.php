<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * AssetFilterRequest
 * 
 * Validates advanced filtering parameters for asset queries.
 * Supports date ranges, multi-select filters, range filters, and location hierarchies.
 * 
 * @package App\Http\Requests
 */
class AssetFilterRequest extends FormRequest
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
            // Search parameters (inherited from SearchAssetRequest functionality)
            'q' => 'nullable|string|min:2|max:200',
            'search_type' => 'nullable|in:name,tag,serial,description,all',

            // Date range filters
            'date_from' => 'nullable|date_format:Y-m-d|before_or_equal:date_to',
            'date_to' => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
            'date_column' => 'nullable|in:created_at,updated_at,purchase_date,warranty_expiry,last_audited_at',

            // Multi-select filters (status)
            'status_id' => 'nullable|array',
            'status_id.*' => 'integer|exists:statuses,id',

            // Multi-select filters (division)
            'division_id' => 'nullable|array',
            'division_id.*' => 'integer|exists:divisions,id',

            // Location hierarchy filter
            'location_id' => 'nullable|integer|exists:locations,id',
            'include_sublocation' => 'nullable|boolean',

            // Multi-select filters (manufacturer)
            'manufacturer_id' => 'nullable|array',
            'manufacturer_id.*' => 'integer|exists:manufacturers,id',

            // Multi-select filters (asset type)
            'asset_type_id' => 'nullable|array',
            'asset_type_id.*' => 'integer|exists:asset_types,id',

            // Price range filter
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0|gte:price_min',

            // Warranty range filter (in months)
            'warranty_months_min' => 'nullable|integer|min:0',
            'warranty_months_max' => 'nullable|integer|min:0|gte:warranty_months_min',

            // Asset model filter
            'asset_model_id' => 'nullable|integer|exists:asset_models,id',

            // Sorting
            'sort_by' => 'nullable|in:id,name,status,division,location,purchase_date,warranty_expiry,purchase_price,created_at,relevance',
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
            'status_id.*.exists' => 'One or more selected statuses do not exist.',
            'division_id.*.exists' => 'One or more selected divisions do not exist.',
            'manufacturer_id.*.exists' => 'One or more selected manufacturers do not exist.',
            'asset_type_id.*.exists' => 'One or more selected asset types do not exist.',
            'location_id.exists' => 'Selected location does not exist.',
            'price_max.gte' => 'Maximum price must be greater than or equal to minimum price.',
            'warranty_months_max.gte' => 'Maximum warranty months must be greater than or equal to minimum.',
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

        // Ensure per_page is never more than 50
        if ($this->has('per_page')) {
            $this->merge([
                'per_page' => min((int)$this->per_page, 50),
            ]);
        }
    }
}
