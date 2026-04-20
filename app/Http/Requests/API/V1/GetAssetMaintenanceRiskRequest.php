<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

class GetAssetMaintenanceRiskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'look_ahead_days' => ['nullable', 'integer', 'min:7', 'max:365'],
            'include_reasons' => ['nullable', 'boolean'],
        ];
    }
}
