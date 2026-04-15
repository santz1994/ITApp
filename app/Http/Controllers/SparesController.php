<?php

namespace App\Http\Controllers;

use App\Division;
use App\Asset;
use App\AssetType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SparesController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth');
  }

  /**
   * Display a listing of spare parts
   */
  public function index()
  {
    $pageTitle = 'Spares Management';
    $divisions = \App\Services\CacheService::getDivisions();
    $assetTypes = AssetType::where('spare', 1)->get();
    
    // Get all assets marked as spares (no pagination - DataTables handles it client-side)
    $assets = Asset::with(['model.asset_type', 'location'])
                   ->whereHas('model.asset_type', function($query) {
                       $query->where('spare', 1);
                   })
                   ->orderBy('asset_tag')
                   ->get();

    return view('spares.index', compact('divisions', 'assetTypes', 'assets', 'pageTitle'));
  }

  /**
   * Show the form for creating a new spare part
   */
  public function create()
  {
      $pageTitle = 'Add New Spare Part';
      $divisions = \App\Services\CacheService::getDivisions();
      $assetTypes = AssetType::where('spare', 1)->get();
      $locations = \App\Location::orderBy('location_name')->get();
      $models = \App\AssetModel::orderBy('asset_model')->get();
      
      return view('spares.create', compact('pageTitle', 'divisions', 'assetTypes', 'locations', 'models'));
  }

  /**
   * Store a newly created spare part in storage
   */
  public function store(Request $request)
  {
      $validated = $request->validate([
          'asset_tag' => 'required|string|unique:assets,asset_tag',
          'name' => 'required|string|max:255',
          'model_id' => 'nullable|exists:asset_models,id',
          'asset_type_id' => 'required|exists:asset_types,id',
          'location_id' => 'nullable|exists:locations,id',
          'qty' => 'nullable|integer|min:0',
          'notes' => 'nullable|string',
      ]);

      try {
          $asset = Asset::create($validated);
          
          Log::info('Spare part created', ['asset_id' => $asset->id, 'user_id' => auth()->id()]);
          
          return redirect()->route('spares.index')
                         ->with('success', 'Spare part added successfully.');
      } catch (\Exception $e) {
          Log::error('Error creating spare part: ' . $e->getMessage());
          return redirect()->back()
                         ->withInput()
                         ->with('error', 'Failed to add spare part: ' . $e->getMessage());
      }
  }

  /**
   * Display the specified spare part
   */
  public function show(Asset $spare)
  {
      $pageTitle = 'Spare Part Details';
      $spare->load(['model', 'location', 'assetType', 'maintenanceLogs']);
      
      return view('spares.show', compact('spare', 'pageTitle'));
  }

  /**
   * Show the form for editing the specified spare part
   */
  public function edit(Asset $spare)
  {
      $pageTitle = 'Edit Spare Part';
      $divisions = \App\Services\CacheService::getDivisions();
      $assetTypes = AssetType::where('spare', 1)->get();
      $locations = \App\Location::orderBy('location_name')->get();
      $models = \App\AssetModel::orderBy('asset_model')->get();
      
      return view('spares.edit', compact('spare', 'pageTitle', 'divisions', 'assetTypes', 'locations', 'models'));
  }

  /**
   * Update the specified spare part in storage
   */
  public function update(Request $request, Asset $spare)
  {
      $validated = $request->validate([
          'asset_tag' => 'required|string|unique:assets,asset_tag,' . $spare->id,
          'name' => 'required|string|max:255',
          'model_id' => 'nullable|exists:asset_models,id',
          'asset_type_id' => 'required|exists:asset_types,id',
          'location_id' => 'nullable|exists:locations,id',
          'qty' => 'nullable|integer|min:0',
          'notes' => 'nullable|string',
      ]);

      try {
          $spare->update($validated);
          
          Log::info('Spare part updated', ['asset_id' => $spare->id, 'user_id' => auth()->id()]);
          
          return redirect()->route('spares.index')
                         ->with('success', 'Spare part updated successfully.');
      } catch (\Exception $e) {
          Log::error('Error updating spare part: ' . $e->getMessage());
          return redirect()->back()
                         ->withInput()
                         ->with('error', 'Failed to update spare part: ' . $e->getMessage());
      }
  }

  /**
   * Remove the specified spare part from storage
   */
  public function destroy(Asset $spare)
  {
      try {
          $spare->delete();
          
          Log::info('Spare part deleted', ['asset_id' => $spare->id, 'user_id' => auth()->id()]);
          
          return redirect()->route('spares.index')
                         ->with('success', 'Spare part deleted successfully.');
      } catch (\Exception $e) {
          Log::error('Error deleting spare part: ' . $e->getMessage());
          return redirect()->back()
                         ->with('error', 'Failed to delete spare part: ' . $e->getMessage());
      }
  }
}
