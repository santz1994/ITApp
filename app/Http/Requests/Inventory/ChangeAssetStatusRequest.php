<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class ChangeAssetStatusRequest extends FormRequest
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
            'status' => 'required|string|in:active,maintenance,retired'
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages()
    {
        return [
            'status.required' => 'Status harus dipilih.',
            'status.in' => 'Status tidak valid. Pilih antara active, maintenance, atau retired.'
        ];
    }
}