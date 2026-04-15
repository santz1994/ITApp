<?php

namespace App\Http\Controllers;

use App\AssetType;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\AssetTypes\StoreAssetTypeRequest;
use App\Http\Requests\AssetTypes\UpdateAssetTypeRequest;
use App\Http\Requests;

class AssetTypesController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth');
  }

  public function index()
  {
    $pageTitle = 'View Asset Types';
    $asset_types = \App\Services\CacheService::getAssetTypes();
    return view('asset-types.index', compact('asset_types', 'pageTitle'));
  }

  public function store(StoreAssetTypeRequest $request)
  {
    $asset_type = AssetType::create($request->validated());

    Session::flash('status', 'success');
    Session::flash('title', $asset_type->type_name);
    Session::flash('message', 'Successfully created');

    return redirect()->route('asset-types.index');
  }

  public function edit(AssetType $asset_type)
  {
    $pageTitle = 'Edit Asset Type - ' . $asset_type->type_name;
    return view('asset-types.edit', compact('asset_type', 'pageTitle'));
  }

  public function update(UpdateAssetTypeRequest $request, AssetType $asset_type)
  {
    $asset_type->update($request->validated());

    Session::flash('status', 'success');
    Session::flash('title', $asset_type->type_name);
    Session::flash('message', 'Successfully updated');

    return redirect()->route('asset-types.index');
  }

  public function destroy(AssetType $asset_type)
  {
    try {
      // Check if asset type has related asset models
      $modelCount = \App\AssetModel::where('asset_type_id', $asset_type->id)->count();
      if ($modelCount > 0) {
        Session::flash('status', 'error');
        Session::flash('title', 'Cannot delete');
        Session::flash('message', 'This asset type has ' . $modelCount . ' asset model(s). Please reassign or remove them first.');
        return redirect()->route('asset-types.index');
      }

      $name = $asset_type->type_name;
      $asset_type->delete();

      Session::flash('status', 'success');
      Session::flash('title', $name);
      Session::flash('message', 'Successfully deleted');
    } catch (\Exception $e) {
      Session::flash('status', 'error');
      Session::flash('title', 'Error');
      Session::flash('message', 'Failed to delete asset type: ' . $e->getMessage());
    }

    return redirect()->route('asset-types.index');
  }
}
