<?php

namespace App\Http\Controllers;

use App\Pcspec;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\Pcspecs\StorePcspecRequest;

class PcspecsController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth');
  }

  public function index()
  {
    $pageTitle = 'PC Specifications';
    $pcspecs = Pcspec::orderBy('cpu')->paginate(50);
    return view('pcspecs.index', compact('pcspecs', 'pageTitle'));
  }

  public function store(StorePcspecRequest $request)
  {
    $pcspec = Pcspec::create($request->validated());

    Session::flash('status', 'success');
    Session::flash('title', $pcspec->cpu . ', ' . $pcspec->ram . ', ' . $pcspec->hdd);
    Session::flash('message', 'Successfully created');

    return redirect()->route('pcspecs.index');
  }

  public function show(Pcspec $pcspec)
  {
    $pageTitle = 'PC Specification Details - ' . $pcspec->cpu;
    return view('pcspecs.show', compact('pcspec', 'pageTitle'));
  }

  public function edit(Pcspec $pcspec)
  {
    $pageTitle = 'Edit PC Specification - ' . $pcspec->cpu . ', ' . $pcspec->ram . ', ' . $pcspec->hdd;
    return view('pcspecs.edit', compact('pcspec', 'pageTitle'));
  }

  public function update(StorePcspecRequest $request, Pcspec $pcspec)
  {
    $pcspec->update($request->validated());

    Session::flash('status', 'success');
    Session::flash('title', $pcspec->cpu . ', ' . $pcspec->ram . ', ' . $pcspec->hdd);
    Session::flash('message', 'Successfully updated');

    return redirect()->route('pcspecs.index');
  }

  public function destroy(Pcspec $pcspec)
  {
    try {
      // Check if pcspec has related assets
      $assetCount = \App\Asset::where('pcspec_id', $pcspec->id)->count();
      if ($assetCount > 0) {
        Session::flash('status', 'error');
        Session::flash('title', 'Cannot delete');
        Session::flash('message', 'This PC specification is assigned to ' . $assetCount . ' asset(s). Please reassign or remove them first.');
        return redirect()->route('pcspecs.index');
      }

      $name = $pcspec->cpu . ', ' . $pcspec->ram . ', ' . $pcspec->hdd;
      $pcspec->delete();

      Session::flash('status', 'success');
      Session::flash('title', $name);
      Session::flash('message', 'Successfully deleted');
    } catch (\Exception $e) {
      Session::flash('status', 'error');
      Session::flash('title', 'Error');
      Session::flash('message', 'Failed to delete PC specification: ' . $e->getMessage());
    }

    return redirect()->route('pcspecs.index');
  }

}
