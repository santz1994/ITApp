<?php

namespace App\Http\Controllers;

use App\TicketsType;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\TicketsTypes\StoreTicketsTypeRequest;
use App\Http\Requests\TicketsTypes\UpdateTicketsTypeRequest;

class TicketsTypesController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth');
  }

  public function index()
  {
    $user = auth()->user();
  // Only super-admin users are allowed to manage ticket types in the legacy app.
  if (!$user || $user->role !== 'super-admin') {
      abort(403);
    }
    $pageTitle = 'Ticket Types';
    $ticketsTypes = \App\Services\CacheService::getTicketTypes();
    return view('admin.ticket-types.index', compact('pageTitle', 'ticketsTypes'));
  }

  public function store(StoreTicketsTypeRequest $request)
  {
    $user = auth()->user();
    if (!$user || $user->role !== 'super-admin') {
      abort(403);
    }
  $ticketsType = TicketsType::create($request->validated());

  Session::flash('status', 'success');
  Session::flash('title', 'Ticket Type: ' . $ticketsType->type);
  Session::flash('message', 'Successfully created');

  return redirect()->route('admin.ticket-types.index')
    ->with('message', 'Successfully created')
    ->with('status', 'success')
    ->with('title', 'Ticket Type: ' . $ticketsType->type);
  }

  public function edit(TicketsType $ticketsType)
  {
    $user = auth()->user();
    if (!$user || $user->role !== 'super-admin') {
      abort(403);
    }
    $pageTitle = 'Edit Ticket Type - ' . $ticketsType->type;
    $ticketsTypes = \App\Services\CacheService::getTicketTypes();
    return view('admin.ticket-types.edit', compact('pageTitle', 'ticketsTypes', 'ticketsType'));
  }

  public function update(UpdateTicketsTypeRequest $request, TicketsType $ticketsType)
  {
    $user = auth()->user();
    if (!$user || $user->role !== 'super-admin') {
      abort(403);
    }
  $ticketsType->update($request->validated());

  Session::flash('status', 'success');
  Session::flash('title', 'Ticket Type: ' . $ticketsType->type);
  Session::flash('message', 'Successfully updated');

  return redirect()->route('admin.ticket-types.index')
    ->with('message', 'Successfully updated')
    ->with('status', 'success')
    ->with('title', 'Ticket Type: ' . $ticketsType->type);
  }

  public function destroy(TicketsType $ticketsType)
  {
    $user = auth()->user();
    if (!$user || $user->role !== 'super-admin') {
      abort(403);
    }

    try {
      $typeName = $ticketsType->type;
      $ticketsType->delete();

      Session::flash('status', 'success');
      Session::flash('title', 'Ticket Type: ' . $typeName);
      Session::flash('message', 'Successfully deleted');

      return redirect()->route('admin.ticket-types.index')
        ->with('message', 'Successfully deleted')
        ->with('status', 'success')
        ->with('title', 'Ticket Type: ' . $typeName);
    } catch (\Exception $e) {
      Session::flash('status', 'error');
      Session::flash('title', 'Delete Error');
      Session::flash('message', 'Cannot delete ticket type: ' . $e->getMessage());

      return redirect()->route('admin.ticket-types.index')
        ->with('message', 'Cannot delete ticket type: ' . $e->getMessage())
        ->with('status', 'error')
        ->with('title', 'Delete Error');
    }
  }
}
