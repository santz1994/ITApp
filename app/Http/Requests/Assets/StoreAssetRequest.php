<?php

namespace App\Http\Requests\Assets;

use App\Http\Requests\Request;

class StoreAssetRequest extends Request
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
    $assetId = null;
    if ($this->route('asset')) {
      $assetParam = $this->route('asset');
      $assetId = is_object($assetParam) ? ($assetParam->id ?? null) : $assetParam;
    }

    return [
          // Accept both `asset_model_id` and legacy `model_id` coming from tests or other callers
          'asset_model_id' => 'sometimes|required|exists:asset_models,id',
          'model_id' => 'sometimes|required|exists:asset_models,id',
          'asset_type_id' => 'required|exists:asset_types,id',
          'division_id' => 'required|exists:divisions,id',
          'supplier_id' => 'required|exists:suppliers,id',
          'warranty_type_id' => 'required|exists:warranty_types,id',
          'invoice_id' => 'nullable|exists:invoices,id',
          'warranty_months' => 'nullable|integer|min:0',
          'notes' => 'nullable|string|max:5000',
          'ip_address' => 'nullable|ip',
          'mac_address' => 'nullable|string|max:255',
          'asset_tag' => $assetId ? 'required|string|max:255|unique:assets,asset_tag,' . $assetId : 'required|string|max:255|unique:assets,asset_tag',
          'name' => 'nullable|string|max:255',
          'serial_number' => $assetId ? 'nullable|string|max:255|unique:assets,serial_number,' . $assetId : 'nullable|string|max:255|unique:assets,serial_number',
          'purchase_date' => 'nullable|date',
          'purchase_cost' => 'nullable|numeric|min:0',
          'location_id' => 'nullable|exists:locations,id',
          'assigned_to' => 'nullable|exists:users,id',
          'status_id' => 'nullable|exists:statuses,id'
        ];
    }

    /**
     * Custom error messages for fields
     *
     * @return array
     */
    public function messages()
    {
      return [
        'asset_model_id.required' => 'Model asset harus dipilih.',
        'model_id.required' => 'Model asset harus dipilih.',
  'asset_type_id.required' => 'Kategori (Tipe Asset) harus dipilih.',
        'asset_model_id.exists' => 'Model asset yang dipilih tidak valid.',
        'model_id.exists' => 'Model asset yang dipilih tidak valid.',
        'division_id.required' => 'Divisi harus dipilih.',
        'division_id.exists' => 'Divisi yang dipilih tidak valid.',
        'supplier_id.required' => 'Supplier harus dipilih.',
        'supplier_id.exists' => 'Supplier yang dipilih tidak valid.',
        'warranty_type_id.required' => 'Jenis garansi harus dipilih.',
        'warranty_type_id.exists' => 'Jenis garansi yang dipilih tidak valid.',
        'asset_tag.required' => 'Tag asset harus diisi.',
        'asset_tag.unique' => 'Tag asset sudah digunakan.',
        'asset_tag.max' => 'Tag asset maksimal 255 karakter.',
        'serial_number.max' => 'Serial number maksimal 255 karakter.',
        'purchase_date.date' => 'Format tanggal pembelian tidak valid.',
        'purchase_cost.numeric' => 'Harga pembelian harus berupa angka.',
        'purchase_cost.min' => 'Harga pembelian tidak boleh negatif.',
        'location_id.exists' => 'Lokasi yang dipilih tidak valid.',
        'assigned_to.exists' => 'Pengguna yang dipilih tidak valid.',
        'status_id.exists' => 'Status yang dipilih tidak valid.'
      ];
    }
}
