<?php

namespace App\Http\Requests\Suppliers;

use App\Http\Requests\Request;

class UpdateSupplierRequest extends Request
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
      $supplier = $this->route()->parameter('supplier');
      // If $supplier is a string (id), fetch the model
      if (is_string($supplier)) {
        $supplier = \App\Supplier::find($supplier);
      }
      $supplierId = $supplier ? $supplier->id : null;
      return [
        'name' => 'required|unique:suppliers,name,' . $supplierId
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
        'name.required' => 'You must enter a Supplier\'s Name.',
        'name.unique' => 'Supplier \'' . $this->name . '\' already exists. You must enter a unique Supplier Name.',
      ];
    }
}
