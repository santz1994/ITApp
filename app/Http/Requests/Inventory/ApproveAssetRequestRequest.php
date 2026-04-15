<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class ApproveAssetRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'approval_notes' => 'nullable|string|max:500'
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages()
    {
        return [
            'approval_notes.max' => 'Catatan persetujuan tidak boleh lebih dari 500 karakter.'
        ];
    }
}