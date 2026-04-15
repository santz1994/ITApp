<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaintenanceLogRequest extends FormRequest
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
            'asset_id' => ['required', 'integer', 'exists:assets,id'],
            'ticket_id' => ['nullable', 'integer', 'exists:tickets,id'],
            'maintenance_type' => ['required', 'string', 'in:preventive,corrective,upgrade,inspection'],
            'description' => ['required', 'string', 'min:10'],
            'performed_by' => ['required', 'integer', 'exists:users,id'],
            'performed_at' => ['required', 'date', 'before_or_equal:now'],
            'cost' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'status' => ['required', 'string', 'in:scheduled,in_progress,completed,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'next_maintenance_date' => ['nullable', 'date', 'after:today'],
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
            'asset_id.required' => 'Please select an asset.',
            'asset_id.exists' => 'The selected asset does not exist.',
            'ticket_id.exists' => 'The selected ticket does not exist.',
            'maintenance_type.required' => 'Please select a maintenance type.',
            'maintenance_type.in' => 'The maintenance type must be one of: preventive, corrective, upgrade, or inspection.',
            'description.required' => 'The maintenance description is required.',
            'description.min' => 'The description must be at least 10 characters.',
            'performed_by.required' => 'Please select who performed the maintenance.',
            'performed_by.exists' => 'The selected user does not exist.',
            'performed_at.required' => 'Please specify when the maintenance was performed.',
            'performed_at.before_or_equal' => 'The maintenance date cannot be in the future.',
            'cost.numeric' => 'The cost must be a valid number.',
            'cost.min' => 'The cost must be at least 0.',
            'cost.max' => 'The cost is too large.',
            'status.required' => 'Please select a status.',
            'status.in' => 'The status must be one of: scheduled, in_progress, completed, or cancelled.',
            'notes.max' => 'Notes may not be greater than 1000 characters.',
            'next_maintenance_date.after' => 'The next maintenance date must be in the future.',
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
            'asset_id' => 'asset',
            'ticket_id' => 'ticket',
            'maintenance_type' => 'maintenance type',
            'description' => 'description',
            'performed_by' => 'performed by',
            'performed_at' => 'date performed',
            'cost' => 'maintenance cost',
            'status' => 'status',
            'notes' => 'notes',
            'next_maintenance_date' => 'next maintenance date',
        ];
    }
}
