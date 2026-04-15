<?php

namespace App\Http\Controllers;

use App\Location;
use App\Http\Requests\Locations\StoreLocationRequest;
use App\Http\Requests\Locations\UpdateLocationRequest;
use App\Repositories\Locations\LocationRepositoryInterface;

class LocationsController extends Controller
{
  protected $location;

  public function __construct(LocationRepositoryInterface $location)
  {
      $this->middleware('auth');
      $this->location = $location;
  }

  public function index()
  {
    $pageTitle = 'Locations';
    $locations = $this->location->getAll();
    return view('locations.index', compact('locations', 'pageTitle'));
  }

  public function store(StoreLocationRequest $request)
  {
    $this->location->store($request);

    $this->location->flashSuccessCreate($this->location->getLatest()->location_name);

    if (env('SLACK_ENABLED')) {
      $this->location->slackCreate();
    }

    return redirect()->route('locations.index');
  }

  public function edit(Location $location)
  {
    $pageTitle = 'Edit Location - ' . $location->location_name;
    return view('locations.edit', compact('location', 'pageTitle'));
  }

  public function update(UpdateLocationRequest $request, Location $location)
  {
    $this->location->update($request, $location);

    $this->location->flashSuccessUpdate($this->location->find($location->id)->location_name);

    if (env('SLACK_ENABLED')) {
      $this->location->slackUpdate($location->id);
    }

    return redirect()->route('locations.index');
  }

  public function destroy(Location $location)
  {
    try {
      // Check if location has related records
      $assetCount = \App\Asset::where('location_id', $location->id)->count();
      if ($assetCount > 0) {
        $this->location->flashError('Cannot delete', 'This location has ' . $assetCount . ' asset(s). Please reassign or remove them first.');
        return redirect()->route('locations.index');
      }

      $name = $location->location_name;
      $location->delete();

      $this->location->flashSuccessDelete($name);

      if (env('SLACK_ENABLED')) {
        $this->location->slackDelete($name);
      }
    } catch (\Exception $e) {
      $this->location->flashError('Error', 'Failed to delete location: ' . $e->getMessage());
    }

    return redirect()->route('locations.index');
  }
}
