<?php

namespace App\Http\Controllers;

use App\TicketsStatus;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\TicketsStatuses\StoreTicketsStatusRequest;
use App\Http\Requests\TicketsStatuses\UpdateTicketsStatusRequest;

class TicketsStatusesController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth');
  }

  public function index()
  {
    $user = auth()->user();
    // Only super-admin may manage ticket statuses in legacy tests
    if (!$user || ($user->role ?? null) !== 'super-admin') {
      abort(403);
    }
    $pageTitle = 'Ticket Statuses';
    $ticketsStatuses = \App\Services\CacheService::getTicketStatuses();
    return view('admin.ticket-statuses.index', compact('pageTitle', 'ticketsStatuses'));
  }

  public function store(StoreTicketsStatusRequest $request)
  {
    $user = auth()->user();
    if (!$user || ($user->role ?? null) !== 'super-admin') {
      abort(403);
    }
  $ticketsStatus = TicketsStatus::create($request->validated());

  Session::flash('status', 'success');
  Session::flash('title', 'Ticket Status: ' . $ticketsStatus->status);
  Session::flash('message', 'Successfully created');

  return redirect()->route('admin.ticket-statuses.index')
    ->with('message', 'Successfully created')
    ->with('status', 'success')
    ->with('title', 'Ticket Status: ' . $ticketsStatus->status);
  }

  public function edit(TicketsStatus $ticketsStatus)
  {
    $user = auth()->user();
    if (!$user || ($user->role ?? null) !== 'super-admin') {
      abort(403);
    }
    $pageTitle = 'Edit Ticket Status - ' . $ticketsStatus->status;
    $ticketsStatuses = \App\Services\CacheService::getTicketStatuses();
    return view('admin.ticket-statuses.edit', compact('pageTitle', 'ticketsStatuses', 'ticketsStatus'));
  }

  public function update(UpdateTicketsStatusRequest $request, TicketsStatus $ticketsStatus)
  {
    $user = auth()->user();
    if (!$user || ($user->role ?? null) !== 'super-admin') {
      abort(403);
    }
  $ticketsStatus->update($request->validated());

  Session::flash('status', 'success');
  Session::flash('title', 'Ticket Status: ' . $ticketsStatus->status);
  Session::flash('message', 'Successfully updated');

  return redirect()->route('admin.ticket-statuses.index')
    ->with('message', 'Successfully updated')
    ->with('status', 'success')
    ->with('title', 'Ticket Status: ' . $ticketsStatus->status);
  }
}
