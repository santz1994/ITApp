<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkResolveConflictsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'resolutions' => 'required|array|min:1',
            'resolutions.*.conflict_id' => 'required|integer|exists:import_conflicts,id',
            'resolutions.*.resolution' => 'required|in:skip,create_new,update_existing,merge',
            'resolutions.*.details' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'resolutions.required' => 'Please provide resolutions for the conflicts',
            'resolutions.array' => 'Resolutions must be an array',
            'resolutions.min' => 'At least one conflict must be resolved',
            'resolutions.*.conflict_id.required' => 'Conflict ID is required for each resolution',
            'resolutions.*.conflict_id.integer' => 'Conflict ID must be an integer',
            'resolutions.*.conflict_id.exists' => 'The conflict does not exist',
            'resolutions.*.resolution.required' => 'Resolution type is required for each conflict',
            'resolutions.*.resolution.in' => 'Invalid resolution type: skip, create_new, update_existing, or merge',
        ];
    }
}
