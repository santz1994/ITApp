<?php

namespace App\Http\Controllers;

use App\AssetModel;
use App\Http\Requests\AssetModels\StoreAssetModelRequest;
use App\Http\Requests\AssetModels\UpdateAssetModelRequest;
use App\Repositories\AssetModels\AssetModelRepositoryInterface;

use Illuminate\Support\Facades\Session;

class AssetModelsController extends Controller
{
    /**
     * @var AssetModelRepositoryInterface
     */
    protected $assetModel;

    public function __construct(AssetModelRepositoryInterface $assetModel)
    {
        $this->middleware('auth');
        $this->assetModel = $assetModel;
    }

  public function index()
  {
    $pageTitle = 'View Models';
      $asset_models = $this->assetModel->getAll(); // Ensure consistent property name
    $manufacturers = $this->assetModel->getAllOrderBy('App\Manufacturer', 'name');
    $asset_types = $this->assetModel->getAllOrderBy('App\AssetType', 'type_name');
    $pcspecs = $this->assetModel->getAllOrderBy('App\Pcspec', 'cpu');
    return view('models.index', compact('asset_models', 'pageTitle', 'manufacturers', 'asset_types', 'pcspecs'));
  }

  public function store(StoreAssetModelRequest $request)
  {
    $this->assetModel->store($request);

    $this->assetModel->flashSuccessCreate($this->assetModel->getLatest()->manufacturer->name . ' - ' . $this->assetModel->getLatest()->asset_model);

    return redirect()->route('models.index');
  }

  public function show(AssetModel $asset_model)
  {
    if (!$asset_model || !$asset_model->id) {
      abort(404, 'Asset Model not found');
    }

    $pageTitle = 'Asset Model Details';
    
    // Get all assets using this model
    $assets = \App\Asset::where('model_id', $asset_model->id)
      ->with(['assignedTo', 'location', 'status'])
      ->orderBy('asset_tag')
      ->get();
    
    return view('models.show', compact('asset_model', 'assets', 'pageTitle'));
  }

  public function edit(AssetModel $asset_model)
  {
  if (!$asset_model || !$asset_model->id) {
    abort(404, 'Asset Model not found');
  }
  $asset_model = \App\AssetModel::find($asset_model->id);
  $manufacturerName = ($asset_model && $asset_model->manufacturer) ? $asset_model->manufacturer->name : '';
  $modelName = ($asset_model && $asset_model->asset_model) ? $asset_model->asset_model : '';
  $pageTitle = 'Edit Model - ' . $manufacturerName . ' ' . $modelName;
  $manufacturers = $this->assetModel->getAllOrderBy('App\Manufacturer', 'name');
  $asset_types = $this->assetModel->getAllOrderBy('App\AssetType', 'type_name');
  $pcspecs = $this->assetModel->getAllOrderBy('App\Pcspec', 'cpu');
  return view('models.edit', compact('asset_model', 'manufacturers', 'asset_types', 'pcspecs', 'pageTitle'));
  }

  public function update(UpdateAssetModelRequest $request, AssetModel $asset_model)
  {
    $this->assetModel->update($request, $asset_model);

    $this->assetModel->flashSuccessUpdate($this->assetModel->find($asset_model->id)->manufacturer->name . ' - ' . $this->assetModel->find($asset_model->id)->asset_model);

    return redirect()->route('models.index');
  }

  public function destroy(AssetModel $asset_model)
  {
    try {
      // Check if asset model has related assets
      $assetCount = \App\Asset::where('model_id', $asset_model->id)->count();
      if ($assetCount > 0) {
        Session::flash('status', 'error');
        Session::flash('title', 'Cannot delete');
        Session::flash('message', 'This asset model is assigned to ' . $assetCount . ' asset(s). Please reassign or remove them first.');
        return redirect()->route('models.index');
      }

      $name = $asset_model->manufacturer->name . ' - ' . $asset_model->asset_model;
      $asset_model->delete();

      Session::flash('status', 'success');
      Session::flash('title', $name);
      Session::flash('message', 'Successfully deleted');
    } catch (\Exception $e) {
      Session::flash('status', 'error');
      Session::flash('title', 'Error');
      Session::flash('message', 'Failed to delete asset model: ' . $e->getMessage());
    }

    return redirect()->route('models.index');
  }

}
