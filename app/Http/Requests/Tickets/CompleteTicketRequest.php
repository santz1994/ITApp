<?php

namespace App\Http\Requests\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class CompleteTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        // Authorization will be handled by controller
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'resolution' => 'nullable|string|max:1000'
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages()
    {
        return [
            'resolution.max' => 'Resolusi tidak boleh lebih dari 1000 karakter.'
        ];
    }
}