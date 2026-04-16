<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FulfillPurchaseRequestRequest extends FormRequest
{
    /**
     * Authorization is enforced by route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for purchase request fulfillment action.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'fulfillment_notes' => 'nullable|string|max:1000',
            'fulfilled_asset_id' => 'nullable|integer|exists:assets,id',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fulfillment_notes.string' => 'Fulfillment notes harus berupa teks.',
            'fulfillment_notes.max' => 'Fulfillment notes maksimal 1000 karakter.',
            'fulfilled_asset_id.integer' => 'Asset fulfillment harus berupa ID numerik.',
            'fulfilled_asset_id.exists' => 'Asset yang dipilih untuk fulfillment tidak ditemukan.',
        ];
    }
}
