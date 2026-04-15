<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class FulfillAssetRequestRequest extends FormRequest
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
            'asset_id' => 'required|exists:assets,id'
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages()
    {
        return [
            'asset_id.required' => 'Asset harus dipilih.',
            'asset_id.exists' => 'Asset yang dipilih tidak valid.'
        ];
    }
}