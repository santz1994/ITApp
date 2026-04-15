<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTicketRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Will be handled by middleware
    }

    public function rules()
    {
        return [
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'ticket_priority_id' => 'nullable|exists:tickets_priorities,id', // Optional - auto-detected
            'ticket_type_id' => 'required|exists:tickets_types,id',
            'location_id' => 'nullable|exists:locations,id', // Optional - auto-filled from user
            'ticket_status_id' => 'nullable|exists:tickets_statuses,id', // Optional - always set to Open
            'sla_due' => 'nullable|date', // Optional - calculated by SLA Learning System
            'asset_id' => 'nullable|exists:assets,id',
            'asset_ids' => 'nullable|array',
            'asset_ids.*' => 'exists:assets,id',
            'user_id' => 'required|exists:users,id'
        ];
    }

    public function messages()
    {
        return [
            'subject.required' => 'Subjek tiket harus diisi',
            'description.required' => 'Deskripsi masalah harus diisi',
            'ticket_type_id.required' => 'Jenis tiket harus dipilih',
            'location_id.exists' => 'Lokasi yang dipilih tidak valid',
            'asset_id.exists' => 'Asset yang dipilih tidak valid',
            'user_id.required' => 'User ID diperlukan'
        ];
    }

    protected function prepareForValidation()
    {
        // Set the authenticated user's ID if not provided
        $userId = auth()->id() ?? $this->user_id;
        $this->merge([
            'user_id' => $userId
        ]);
        
        // Auto-set location from user's location if not provided
        if (empty($this->location_id) && $userId) {
            $user = \App\User::find($userId);
            if ($user && $user->location_id) {
                $this->merge([
                    'location_id' => $user->location_id
                ]);
            }
        }
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
            // Description should have reasonable length
            if ($this->filled('description') && strlen($this->description) < 10) {
                $validator->errors()->add('description', 'Description should be at least 10 characters to properly describe the issue.');
            }

            // If assets are selected, verify they're not already in "In Repair" status
            if ($this->filled('asset_ids') && is_array($this->asset_ids)) {
                $assetsInRepair = \App\Asset::whereIn('id', $this->asset_ids)
                    ->whereIn('status_id', [3, 4]) // Out for Repairs, Waiting for Repairs
                    ->exists();
                
                if ($assetsInRepair) {
                    $validator->errors()->add('asset_ids', 'One or more selected assets are already marked as "In Repair". Please check the asset status.');
                }
            }

            // Subject should not be too short
            if ($this->filled('subject') && strlen($this->subject) < 5) {
                $validator->errors()->add('subject', 'Subject should be at least 5 characters.');
            }

            // Verify ticket_status_id is valid if provided
            if ($this->filled('ticket_status_id')) {
                $status = \App\TicketsStatus::find($this->ticket_status_id);
                if (!$status) {
                    $validator->errors()->add('ticket_status_id', 'Invalid ticket status selected.');
                }
            }
        });
    }
}