<?php

namespace App\Http\Controllers;

use App\Location;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\Storerooms\UpdateStoreroomRequest;

class StoreroomsController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth');
  }

  public function index()
  {
    $pageTitle = 'Default Storeroom';
    $locations = \App\Services\CacheService::getLocations();
    $storeroom = Location::where('storeroom', 1)->first();
    return view('admin.storeroom.index', compact('pageTitle', 'locations', 'storeroom'));
  }

  public function update(UpdateStoreroomRequest $request)
  {
    $oldStoreroom = Location::where('storeroom', 1)->first();
    if ($oldStoreroom) {
        $oldStoreroom->storeroom = 0;
        $oldStoreroom->save();
    }

    // Be defensive: legacy clients/tests may send the selected value under
    // different parameter names or as an empty value. Try several sources.
    $storeId = $request->input('store') ?? $request->input('store_id') ?? request()->get('store');

    $location = Location::find($storeId);
    if ($location) {
        $location->storeroom = 1;
        $location->save();

    Session::flash('status', 'success');
    Session::flash('title', 'New Default Storeroom Saved');
    Session::flash('message', $location->location_name);
  } else {
    Session::flash('status', 'error');
    Session::flash('title', 'Storeroom Not Found');
    Session::flash('message', 'The selected storeroom does not exist.');
  }

  return redirect()->route('admin.storeroom.index');
  }
}
