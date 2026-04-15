<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class RejectAssetRequestRequest extends FormRequest
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
            'approval_notes' => 'required|string|max:500'
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages()
    {
        return [
            'approval_notes.required' => 'Catatan penolakan harus diisi.',
            'approval_notes.max' => 'Catatan penolakan tidak boleh lebih dari 500 karakter.'
        ];
    }
}