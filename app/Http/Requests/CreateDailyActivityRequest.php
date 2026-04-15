<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDailyActivityRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Will be handled by middleware
    }

    public function rules()
    {
        return [
            'activity_date' => 'required|date|before_or_equal:today',
            'description' => 'required|string|min:10|max:1000',
            'activity_type' => 'required|string|in:ticket_handling,asset_management,user_support,system_maintenance,documentation,training,meeting,project_work,monitoring,other',
            'user_id' => 'required|exists:users,id',
            'ticket_id' => 'nullable|exists:tickets,id'
        ];
    }

    public function messages()
    {
        return [
            'activity_date.required' => 'Tanggal aktivitas harus diisi',
            'activity_date.date' => 'Format tanggal tidak valid',
            'activity_date.before_or_equal' => 'Tanggal aktivitas tidak boleh lebih dari hari ini',
            'description.required' => 'Deskripsi aktivitas harus diisi',
            'description.min' => 'Deskripsi minimal 10 karakter',
            'description.max' => 'Deskripsi maksimal 1000 karakter',
            'user_id.required' => 'User ID diperlukan',
            'user_id.exists' => 'User tidak valid',
            'ticket_id.exists' => 'Ticket ID tidak valid'
        ];
    }

    protected function prepareForValidation()
    {
        // Set default values
        $this->merge([
            'type' => $this->ticket_id ? 'auto_from_ticket' : 'manual',
            'user_id' => auth()->id() ?? $this->user_id
        ]);
    }
}