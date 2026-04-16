<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectPurchaseRequestRequest extends FormRequest
{
    /**
     * Authorization is enforced by route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for purchase request rejection action.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'admin_notes' => 'required|string|max:1000',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'admin_notes.required' => 'Reason for rejection wajib diisi.',
            'admin_notes.string' => 'Reason for rejection harus berupa teks.',
            'admin_notes.max' => 'Reason for rejection maksimal 1000 karakter.',
        ];
    }
}
