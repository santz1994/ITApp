<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\WarrantyType;
use Illuminate\Support\Facades\Log;

class WarrantyTypesController extends Controller
{
    /**
     * Store a newly created warranty type in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:warranty_types,name'
        ]);

        try {
            $warrantyType = WarrantyType::create([
                'name' => $request->input('name')
            ]);

            Log::info('Warranty type created successfully', ['warranty_type' => $warrantyType]);
            return redirect()->back()->with('success', 'Warranty type "' . $warrantyType->name . '" created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create warranty type', [
                'error' => $e->getMessage(),
                'input' => $request->only('name')
            ]);
            return redirect()->back()->with('error', 'Failed to create warranty type: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update the specified warranty type in storage.
     */
    public function update(Request $request, WarrantyType $warrantyType)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:warranty_types,name,' . $warrantyType->id
        ]);

        try {
            $oldName = $warrantyType->name;
            $warrantyType->update([
                'name' => $request->input('name')
            ]);

            Log::info('Warranty type updated successfully', [
                'old_name' => $oldName,
                'new_name' => $warrantyType->name
            ]);
            return redirect()->back()->with('success', 'Warranty type updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update warranty type', [
                'warranty_type_id' => $warrantyType->id,
                'error' => $e->getMessage(),
                'input' => $request->only('name')
            ]);
            return redirect()->back()->with('error', 'Failed to update warranty type: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified warranty type from storage.
     */
    public function destroy(WarrantyType $warrantyType)
    {
        try {
            // Check if warranty type is being used by any assets
            if ($warrantyType->asset()->count() > 0) {
                return redirect()->back()->with('error', 'Cannot delete warranty type because it is being used by assets.');
            }

            $warrantyType->delete();

            return redirect()->back()->with('success', 'Warranty type deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete warranty type: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete warranty type: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified warranty type.
     */
    public function edit(WarrantyType $warrantyType)
    {
        return response()->json($warrantyType);
    }
}
