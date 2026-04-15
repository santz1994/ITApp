<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware/policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $assetId = $this->route('asset') ? $this->route('asset')->id : $this->route('id');

        return [
            'asset_tag' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('assets', 'asset_tag')->ignore($assetId)
            ],
            'serial_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('assets', 'serial_number')
                    ->ignore($assetId)
                    ->whereNotNull('serial_number')  // Fix: Allow multiple NULLs in database
            ],
            'model_id' => ['sometimes', 'required', 'integer', 'exists:asset_models,id'],
            'division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'purchase_date' => ['nullable', 'date', 'before_or_equal:today'],
            'warranty_months' => ['nullable', 'integer', 'min:0', 'max:120'],
            'warranty_type_id' => ['nullable', 'integer', 'exists:warranty_types,id'],
            'invoice_id' => ['nullable', 'integer', 'exists:invoices,id'],
            'ip_address' => ['nullable', 'ip'],
            'mac_address' => ['nullable', 'regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/'],
            'status_id' => ['sometimes', 'required', 'integer', 'exists:statuses,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'asset_tag.required' => 'The asset tag is required.',
            'asset_tag.unique' => 'This asset tag already exists in the system.',
            'serial_number.unique' => 'This serial number already exists in the system.',
            'model_id.required' => 'Please select an asset model.',
            'model_id.exists' => 'The selected asset model is invalid.',
            'division_id.exists' => 'The selected division is invalid.',
            'supplier_id.exists' => 'The selected supplier is invalid.',
            'purchase_date.before_or_equal' => 'The purchase date cannot be in the future.',
            'warranty_months.min' => 'Warranty months must be at least 0.',
            'warranty_months.max' => 'Warranty months cannot exceed 120 (10 years).',
            'warranty_type_id.exists' => 'The selected warranty type is invalid.',
            'invoice_id.exists' => 'The selected invoice is invalid.',
            'ip_address.ip' => 'Please enter a valid IP address.',
            'mac_address.regex' => 'Please enter a valid MAC address (e.g., AA:BB:CC:DD:EE:FF).',
            'status_id.required' => 'Please select a status.',
            'status_id.exists' => 'The selected status is invalid.',
            'assigned_to.exists' => 'The selected user does not exist.',
            'notes.max' => 'Notes may not be greater than 1000 characters.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'asset_tag' => 'asset tag',
            'serial_number' => 'serial number',
            'model_id' => 'asset model',
            'division_id' => 'division',
            'supplier_id' => 'supplier',
            'purchase_date' => 'purchase date',
            'warranty_months' => 'warranty period',
            'warranty_type_id' => 'warranty type',
            'invoice_id' => 'invoice',
            'ip_address' => 'IP address',
            'mac_address' => 'MAC address',
            'status_id' => 'status',
            'assigned_to' => 'assigned user',
            'notes' => 'notes',
        ];
    }
}
