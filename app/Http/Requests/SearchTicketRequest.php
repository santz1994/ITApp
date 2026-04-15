<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'q' => 'required|string|min:2|max:200',
            'type' => 'nullable|string|in:subject,description,code,all',
            'sort' => 'nullable|string|in:relevance,date,priority',
            'status_id' => 'nullable|integer|exists:tickets_statuses,id',
            'priority_id' => 'nullable|integer|exists:tickets_priorities,id',
            'assigned_to' => 'nullable|integer|exists:users,id',
            'user_id' => 'nullable|integer|exists:users,id',
            'include_resolved' => 'nullable|boolean',
            'per_page' => 'nullable|integer|between:1,50',
            'page' => 'nullable|integer|min:1',
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
            'q.required' => 'Search query is required',
            'q.min' => 'Search query must be at least 2 characters',
            'q.max' => 'Search query cannot exceed 200 characters',
            'type.in' => 'Invalid search type. Must be one of: subject, description, code, all',
            'sort.in' => 'Invalid sort option. Must be one of: relevance, date, priority',
            'per_page.between' => 'Results per page must be between 1 and 50',
        ];
    }

    /**
     * Get the search parameters.
     *
     * @return array
     */
    public function getSearchParams()
    {
        $data = $this->validated();
        
        // Set defaults
        $data['type'] = $data['type'] ?? 'all';
        $data['sort'] = $data['sort'] ?? 'relevance';
        $data['per_page'] = $data['per_page'] ?? 20;
        $data['page'] = $data['page'] ?? 1;
        $data['include_resolved'] = $data['include_resolved'] ?? false;
        
        return $data;
    }
}
