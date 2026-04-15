<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DailyActivityRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'activity_type' => 'nullable|string|max:100',
            'activity' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'activity_date' => 'required|date',
            'description' => 'nullable|string|max:1000',
            'ticket_id' => 'nullable|exists:tickets,id',
            'duration_minutes' => 'nullable|integer|min:0',
            'type' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'status' => 'nullable|in:pending,in_progress,completed',
        ];
    }
}
