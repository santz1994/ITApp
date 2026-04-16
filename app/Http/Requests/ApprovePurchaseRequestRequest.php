<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApprovePurchaseRequestRequest extends FormRequest
{
    /**
     * Authorization is enforced by route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for purchase request approval action.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'admin_notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'admin_notes.string' => 'Approval notes harus berupa teks.',
            'admin_notes.max' => 'Approval notes maksimal 1000 karakter.',
        ];
    }
}