<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class StoreAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'asset_tag' => ['required', 'string', 'max:50', 'unique:assets,asset_tag'],
            'serial_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('assets', 'serial_number')
                    ->ignore($this->route('asset') ? $this->route('asset')->id : null)
                    ->whereNotNull('serial_number')  // Fix: Allow multiple NULLs in database
            ],
            'asset_type_id' => ['nullable', 'integer', 'exists:asset_types,id'],  // Used only for frontend filtering
            'model_id' => ['nullable', 'integer', 'exists:asset_models,id'],
            'division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'purchase_date' => ['nullable', 'date', 'before_or_equal:today'],
            'warranty_months' => ['nullable', 'integer', 'min:0', 'max:84'],  // 84 months = 7 years max
            'warranty_type_id' => ['nullable', 'integer', 'exists:warranty_types,id'],
            'invoice_id' => ['nullable', 'integer', 'exists:invoices,id'],
            'purchase_order_id' => ['nullable', 'integer', 'exists:purchase_orders,id'],
            'ip_address' => ['nullable', 'ip'],
            'mac_address' => ['nullable', 'regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/'],
            'status_id' => ['required', 'integer', 'exists:statuses,id'],
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
            'asset_tag.max' => 'Asset tag may not be greater than 50 characters.',
            'serial_number.unique' => 'This serial number already exists in the system.',
            'asset_type_id.exists' => 'The selected asset type is invalid.',
            'model_id.exists' => 'The selected asset model is invalid.',
            'division_id.exists' => 'The selected division is invalid.',
            'supplier_id.exists' => 'The selected supplier is invalid.',
            'location_id.exists' => 'The selected location is invalid.',
            'purchase_date.before_or_equal' => 'The purchase date cannot be in the future.',
            'warranty_months.min' => 'Warranty months must be at least 0.',
            'warranty_months.max' => 'Warranty months cannot exceed 84 months (7 years).',
            'warranty_type_id.exists' => 'The selected warranty type is invalid.',
            'invoice_id.exists' => 'The selected invoice is invalid.',
            'purchase_order_id.exists' => 'The selected purchase order is invalid.',
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
            'asset_type_id' => 'asset type',
            'model_id' => 'asset model',
            'division_id' => 'division',
            'supplier_id' => 'supplier',
            'location_id' => 'location',
            'purchase_date' => 'purchase date',
            'warranty_months' => 'warranty period',
            'warranty_type_id' => 'warranty type',
            'invoice_id' => 'invoice',
            'purchase_order_id' => 'purchase order',
            'ip_address' => 'IP address',
            'mac_address' => 'MAC address',
            'status_id' => 'status',
            'assigned_to' => 'assigned user',
            'notes' => 'notes',
        ];
    }

    /**
     * Configure the validator instance with cross-field validation.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // If warranty_months is provided, warranty_type_id should also be provided
            if ($this->filled('warranty_months') && $this->warranty_months > 0 && !$this->filled('warranty_type_id')) {
                $validator->errors()->add('warranty_type_id', 'Please select a warranty type when warranty period is specified.');
            }

            // If IP address is provided for non-computer assets, warn (but don't fail)
            if ($this->filled('ip_address') && $this->filled('model_id')) {
                $model = \App\AssetModel::find($this->model_id);
                if ($model && $model->asset_type_id) {
                    $assetType = \App\AssetType::find($model->asset_type_id);
                    if ($assetType && !stripos($assetType->type_name, 'computer') && !stripos($assetType->type_name, 'pc') && !stripos($assetType->type_name, 'laptop')) {
                        // Just log a warning, don't fail validation
                        Log::info("IP address provided for non-computer asset type: {$assetType->type_name}");
                    }
                }
            }

            // If purchase_order_id is provided, verify supplier matches
            if ($this->filled('purchase_order_id') && $this->filled('supplier_id')) {
                $po = \App\PurchaseOrder::find($this->purchase_order_id);
                if ($po && $po->supplier_id != $this->supplier_id) {
                    $validator->errors()->add('purchase_order_id', 'The purchase order supplier must match the selected supplier.');
                }
            }
        });
    }
}
