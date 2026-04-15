<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

/**
 * AssetBulkUpdateRequest
 *
 * Validates bulk update operations for assets
 * Supports:
 * - Bulk status updates
 * - Bulk assignments
 * - Bulk field modifications
 *
 * Validation rules ensure:
 * - Asset IDs are valid and exist
 * - Update values are valid for their fields
 * - Operation is allowed (authorization checks)
 */
class AssetBulkUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Bulk updates require 'update_assets' permission
        return $this->user() && $this->user()->can('update', \App\Asset::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Asset IDs: array of integers
            'asset_ids' => 'required|array|min:1|max:10000',
            'asset_ids.*' => 'integer|distinct',

            // Status update fields
            'status_id' => 'nullable|integer|exists:statuses,id',

            // Assignment fields
            'assigned_to' => 'nullable|integer|exists:users,id',
            'department_id' => 'nullable|integer|exists:divisions,id',

            // Generic field updates
            'updates' => 'nullable|array',
            'updates.location_id' => 'nullable|integer|exists:locations,id',
            'updates.manufacturer_id' => 'nullable|integer|exists:manufacturers,id',
            'updates.asset_type_id' => 'nullable|integer|exists:asset_types,id',
            'updates.warranty_expiry_date' => 'nullable|date|after_or_equal:today',
            'updates.purchase_date' => 'nullable|date|before_or_equal:today',
            'updates.notes' => 'nullable|string|max:5000',
            'updates.serial_number' => 'nullable|string|max:255',
            'updates.model' => 'nullable|string|max:255',

            // Operation control
            'dry_run' => 'nullable|boolean',
            'reason' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'asset_ids.required' => 'At least one asset ID is required',
            'asset_ids.array' => 'Asset IDs must be an array',
            'asset_ids.min' => 'At least one asset must be selected',
            'asset_ids.max' => 'Maximum 10,000 assets per operation',
            'asset_ids.*.integer' => 'Each asset ID must be an integer',
            'asset_ids.*.distinct' => 'Duplicate asset IDs are not allowed',

            'status_id.exists' => 'Invalid status ID',
            'assigned_to.exists' => 'The assigned user does not exist',
            'department_id.exists' => 'Invalid department ID',

            'updates.array' => 'Updates must be an array of field-value pairs',
            'updates.location_id.exists' => 'Invalid location ID',
            'updates.manufacturer_id.exists' => 'Invalid manufacturer ID',
            'updates.asset_type_id.exists' => 'Invalid asset type ID',
            'updates.warranty_expiry_date.date' => 'Warranty expiry date must be a valid date',
            'updates.warranty_expiry_date.after_or_equal' => 'Warranty expiry date cannot be in the past',
            'updates.purchase_date.date' => 'Purchase date must be a valid date',
            'updates.purchase_date.before_or_equal' => 'Purchase date cannot be in the future',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Remove duplicates from asset_ids
        if ($this->has('asset_ids')) {
            $this->merge([
                'asset_ids' => array_unique((array)$this->asset_ids)
            ]);
        }

        // Convert dry_run string to boolean
        if ($this->has('dry_run')) {
            $dryRun = $this->dry_run;
            $this->merge([
                'dry_run' => in_array($dryRun, [true, 'true', '1', 1], true)
            ]);
        }
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, response()->json([
            'message' => 'Validation failed for bulk update operation',
            'errors' => $validator->errors()
        ], 422));
    }

    /**
     * Get filter parameters as array
     * Used by controller to pass to bulk operation trait
     *
     * @return array
     */
    public function getBulkUpdateParams()
    {
        return [
            'asset_ids' => $this->asset_ids,
            'status_id' => $this->status_id,
            'assigned_to' => $this->assigned_to,
            'department_id' => $this->department_id,
            'updates' => $this->updates,
            'dry_run' => $this->dry_run ?? false,
            'reason' => $this->reason,
            'user_id' => $this->user()->id
        ];
    }
}
