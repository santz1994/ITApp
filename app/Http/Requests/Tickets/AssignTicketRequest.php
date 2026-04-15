<?php

namespace App\Http\Requests\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class AssignTicketRequest extends FormRequest
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
            'admin_id' => 'required|exists:users,id'
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages()
    {
        return [
            'admin_id.required' => 'Admin harus dipilih.',
            'admin_id.exists' => 'Admin yang dipilih tidak valid.'
        ];
    }
}