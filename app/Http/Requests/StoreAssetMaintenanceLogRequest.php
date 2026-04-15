<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssetMaintenanceLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (user_has_role(auth()->user(), 'admin') || user_has_role(auth()->user(), 'super-admin'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'asset_id' => 'required|exists:assets,id',
            'ticket_id' => 'nullable|exists:tickets,id',
            'maintenance_type' => 'required|in:repair,preventive,upgrade,inspection,other',
            'description' => 'required|string|min:10|max:1000',
            'part_name' => 'nullable|string|max:255',
            'parts_used' => 'nullable|array',
            'parts_used.*' => 'string|max:255',
            'cost' => 'nullable|numeric|min:0|max:999999.99',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'scheduled_at' => 'nullable|date|after:now',
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date|after:started_at',
            'notes' => 'nullable|string|max:2000'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'asset_id.required' => 'Asset harus dipilih.',
            'asset_id.exists' => 'Asset yang dipilih tidak valid.',
            'ticket_id.exists' => 'Ticket yang dipilih tidak valid.',
            'maintenance_type.required' => 'Tipe maintenance harus dipilih.',
            'maintenance_type.in' => 'Tipe maintenance tidak valid.',
            'description.required' => 'Deskripsi maintenance harus diisi.',
            'description.min' => 'Deskripsi maintenance minimal 10 karakter.',
            'cost.numeric' => 'Biaya harus berupa angka.',
            'cost.min' => 'Biaya tidak boleh negatif.',
            'scheduled_at.after' => 'Jadwal maintenance harus di masa depan.',
            'completed_at.after' => 'Waktu selesai harus setelah waktu mulai.'
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->status === 'completed' && !$this->completed_at) {
                $validator->errors()->add('completed_at', 'Waktu selesai harus diisi jika status adalah completed.');
            }
            
            if ($this->status === 'in_progress' && !$this->started_at) {
                $validator->errors()->add('started_at', 'Waktu mulai harus diisi jika status adalah in_progress.');
            }
        });
    }
}
