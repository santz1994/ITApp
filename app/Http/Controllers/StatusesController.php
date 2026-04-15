<?php

namespace App\Http\Controllers;

use App\Status;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\Statuses\StoreStatusRequest;
use App\Http\Requests\Statuses\UpdateStatusRequest;
use App\Traits\RoleBasedAccessTrait;

class StatusesController extends Controller
{
  use RoleBasedAccessTrait;
  
  public function __construct()
  {
      $this->middleware('auth');
      $this->middleware('role:super-admin');
  }

  public function index()
  {
    // No need for role check here - middleware handles it
    $pageTitle = 'Statuses';
    $statuses = \App\Services\CacheService::getStatuses();
    return view('admin.assets-statuses.index', compact('statuses', 'pageTitle'));
  }

  public function store(StoreStatusRequest $request)
  {
    $status = Status::create($request->validated());

    Session::flash('status', 'success');
    Session::flash('title', $status->name);
    Session::flash('message', 'Successfully created');

    return redirect()->route('system-settings.asset-statuses');
  }

  public function edit(Status $status)
  {
    // No need for role check here - middleware handles it
    $pageTitle = 'Edit Status - ' . $status->name;
    return view('admin.assets-statuses.edit', compact('status', 'pageTitle'));
  }

  public function update(UpdateStatusRequest $request, Status $status)
  {
    $status->update($request->validated());

    Session::flash('status', 'success');
    Session::flash('title', $status->name);
    Session::flash('message', 'Successfully updated');

    return redirect()->route('system-settings.asset-statuses');
  }

  public function destroy(Status $status)
  {
    $statusName = $status->name;
    $status->delete();

    Session::flash('status', 'success');
    Session::flash('title', $statusName);
    Session::flash('message', 'Successfully deleted');

    return redirect()->route('system-settings.asset-statuses');
  }
}
