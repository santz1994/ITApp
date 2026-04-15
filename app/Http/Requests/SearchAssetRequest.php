<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchAssetRequest extends FormRequest
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
            'type' => 'nullable|string|in:name,tag,serial,all',
            'sort' => 'nullable|string|in:relevance,name,date',
            'status_id' => 'nullable|integer|exists:statuses,id',
            'division_id' => 'nullable|integer|exists:divisions,id',
            'location_id' => 'nullable|integer|exists:locations,id',
            'manufacturer_id' => 'nullable|integer|exists:manufacturers,id',
            'assigned_to' => 'nullable|integer|exists:users,id',
            'per_page' => 'nullable|integer|between:1,50',
            'page' => 'nullable|integer|min:1',
            'active' => 'nullable|boolean',
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
            'type.in' => 'Invalid search type. Must be one of: name, tag, serial, all',
            'sort.in' => 'Invalid sort option. Must be one of: relevance, name, date',
            'per_page.between' => 'Results per page must be between 1 and 50',
        ];
    }

    /**
     * Get the query parameters.
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
        
        return $data;
    }
}
