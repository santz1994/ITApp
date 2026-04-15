<?php

namespace App\Http\Controllers;

use App\Manufacturer;
use App\Http\Requests\Manufacturers\StoreManufacturerRequest;
use App\Http\Requests\Manufacturers\UpdateManufacturerRequest;
use App\Repositories\Manufacturers\ManufacturerRepositoryInterface;

use Illuminate\Support\Facades\Session;

class ManufacturersController extends Controller
{
  /**
   * Repository instance.
   *
   * @var \App\Repositories\Manufacturers\ManufacturerRepositoryInterface
   */
  protected $manufacturer;

  public function __construct(ManufacturerRepositoryInterface $manufacturer)
  {
      $this->middleware('auth');
      $this->manufacturer = $manufacturer;
  }

  public function index()
  {
    $pageTitle = 'Manufacturers';
    $manufacturers = $this->manufacturer->getAll();
    return view('manufacturers.index', compact('manufacturers', 'pageTitle'));
  }

  public function store(StoreManufacturerRequest $request)
  {
    $this->manufacturer->store($request);

    $this->manufacturer->flashSuccessCreate($this->manufacturer->getLatest()->name);

    return redirect()->route('manufacturers.index');
  }

  public function show(Manufacturer $manufacturer)
  {
    $pageTitle = 'Manufacturer Details - ' . $manufacturer->name;
    return view('manufacturers.show', compact('manufacturer', 'pageTitle'));
  }

  public function edit(Manufacturer $manufacturer)
  {
    $pageTitle = 'Edit Manufacturer - ' . $manufacturer->name;
    return view('manufacturers.edit', compact('manufacturer', 'pageTitle'));
  }

  public function update(UpdateManufacturerRequest $request, Manufacturer $manufacturer)
  {
    $this->manufacturer->update($request, $manufacturer);

    $this->manufacturer->flashSuccessUpdate($this->manufacturer->find($manufacturer->id)->name);

    return redirect()->route('manufacturers.index');
  }

  public function destroy(Manufacturer $manufacturer)
  {
    try {
      // Check if manufacturer has related asset models
      $modelCount = \App\AssetModel::where('manufacturer_id', $manufacturer->id)->count();
      if ($modelCount > 0) {
        Session::flash('status', 'error');
        Session::flash('title', 'Cannot delete');
        Session::flash('message', 'This manufacturer has ' . $modelCount . ' asset model(s). Please reassign or remove them first.');
        return redirect()->route('manufacturers.index');
      }

      $name = $manufacturer->name;
      $manufacturer->delete();

      Session::flash('status', 'success');
      Session::flash('title', $name);
      Session::flash('message', 'Successfully deleted');
    } catch (\Exception $e) {
      Session::flash('status', 'error');
      Session::flash('title', 'Error');
      Session::flash('message', 'Failed to delete manufacturer: ' . $e->getMessage());
    }

    return redirect()->route('manufacturers.index');
  }
}
