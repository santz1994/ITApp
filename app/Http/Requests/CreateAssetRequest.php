<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAssetRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Will be handled by middleware
    }

    public function rules()
    {
        return [
            'asset_tag' => 'required|string|max:10|unique:assets,asset_tag',
            'serial_number' => 'required|string|max:255',
            'model_id' => 'required|exists:asset_models,id',
            'division_id' => 'required|exists:divisions,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'warranty_months' => 'nullable|integer|min:0|max:120',
            'warranty_type_id' => 'nullable|exists:warranty_types,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'ip_address' => 'nullable|ip',
            'mac_address' => 'nullable|string|regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/',
            'status_id' => 'required|exists:statuses,id',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:1000'
        ];
    }

    public function messages()
    {
        return [
            'asset_tag.required' => 'Asset Tag harus diisi',
            'asset_tag.unique' => 'Asset Tag sudah digunakan',
            'model_id.required' => 'Model asset harus dipilih',
            'division_id.required' => 'Divisi harus dipilih',
            'supplier_id.required' => 'Supplier harus dipilih',
            'status_id.required' => 'Status asset harus dipilih',
            'ip_address.ip' => 'Format IP address tidak valid',
            'mac_address.regex' => 'Format MAC address tidak valid (contoh: AA:BB:CC:DD:EE:FF)',
            'warranty_months.integer' => 'Warranty harus berupa angka',
            'warranty_months.max' => 'Warranty maksimal 120 bulan (10 tahun)',
            'notes.max' => 'Notes maksimal 1000 karakter'
        ];
    }

    protected function prepareForValidation()
    {
        // Auto-generate QR code if not provided
        $this->merge([
            'qr_code' => $this->qr_code ?? $this->generateQRCode(),
            'status_id' => $this->status_id ?? 2 // Default: In Stock
        ]);
    }

    private function generateQRCode()
    {
        return 'AST-' . strtoupper(uniqid());
    }
}