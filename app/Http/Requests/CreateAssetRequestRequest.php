<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAssetRequestRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Will be handled by middleware
    }

    public function rules()
    {
        return [
            'asset_type_id' => 'required|exists:asset_types,id',
            'title' => 'required|string|max:255',
            'justification' => 'required|string|min:10|max:1000',
            'requested_by' => 'required|exists:users,id',
            'requested_quantity' => 'nullable|integer|min:1',
            'unit' => 'nullable|string|max:50',
            'division' => 'nullable|string|max:100',
            'priority' => 'nullable|in:low,medium,high,urgent'
        ];
    }

    public function messages()
    {
        return [
            'asset_type_id.required' => 'Jenis asset yang diminta harus dipilih',
            'asset_type_id.exists' => 'Jenis asset tidak valid',
            'justification.required' => 'Justifikasi permintaan asset harus diisi',
            'justification.min' => 'Justifikasi minimal 10 karakter',
            'justification.max' => 'Justifikasi maksimal 1000 karakter',
            'requested_by.required' => 'User ID diperlukan',
            'requested_by.exists' => 'User tidak valid'
        ];
    }

    protected function prepareForValidation()
    {
        // Set default values
        $this->merge([
            'status' => 'pending',
            'requested_by' => auth()->id() ?? $this->requested_by
        ]);
    }
}