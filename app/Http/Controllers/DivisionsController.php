<?php

namespace App\Http\Controllers;

use App\Division;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\Divisions\StoreDivisionRequest;
use App\Http\Requests\Divisions\UpdateDivisionRequest;

class DivisionsController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth');
  }

  public function index()
  {
    $pageTitle = 'Divisions';
    $divisions = \App\Services\CacheService::getDivisions();
    return view('divisions.index', compact('divisions', 'pageTitle'));
  }

  public function store(StoreDivisionRequest $request)
  {
    $division = Division::create($request->validated());

    Session::flash('status', 'success');
    Session::flash('title', $division->name);
    Session::flash('message', 'Successfully created');

    return redirect()->route('divisions.index');
  }

  public function show(Division $division)
  {
    $pageTitle = 'Division Details - ' . $division->name;
    return view('divisions.show', compact('division', 'pageTitle'));
  }

  public function edit(Division $division)
  {
    $pageTitle = 'Edit Division - ' . $division->name;
    return view('divisions.edit', compact('division', 'pageTitle'));
  }

  public function update(UpdateDivisionRequest $request, Division $division)
  {
    $division->update($request->validated());

    Session::flash('status', 'success');
    Session::flash('title', $division->name);
    Session::flash('message', 'Successfully updated');

    return redirect()->route('divisions.index');
  }

  public function destroy(Division $division)
  {
    try {
      // Check if division has related users
      $userCount = \App\User::where('division_id', $division->id)->count();
      if ($userCount > 0) {
        Session::flash('status', 'error');
        Session::flash('title', 'Cannot delete');
        Session::flash('message', 'This division has ' . $userCount . ' user(s). Please reassign or remove them first.');
        return redirect()->route('divisions.index');
      }

      // Check if division has related assets
      $assetCount = \App\Asset::where('division_id', $division->id)->count();
      if ($assetCount > 0) {
        Session::flash('status', 'error');
        Session::flash('title', 'Cannot delete');
        Session::flash('message', 'This division has ' . $assetCount . ' asset(s). Please reassign or remove them first.');
        return redirect()->route('divisions.index');
      }

      $name = $division->name;
      $division->delete();

      Session::flash('status', 'success');
      Session::flash('title', $name);
      Session::flash('message', 'Successfully deleted');
    } catch (\Exception $e) {
      Session::flash('status', 'error');
      Session::flash('title', 'Error');
      Session::flash('message', 'Failed to delete division: ' . $e->getMessage());
    }

    return redirect()->route('divisions.index');
  }
}
