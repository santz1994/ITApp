<?php

namespace App\Http\Controllers;

use App\TicketsCannedField;
use App\TicketsPriority;
use App\TicketsStatus;
use App\TicketsType;
use App\Location;
use App\User;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\TicketsCannedFields\StoreTicketsCannedFieldRequest;
use Illuminate\Http\Request;

class TicketsCannedFieldsController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth');
  }

  public function index()
  {
    $user = auth()->user();
    if (!$user || $user->role !== 'super-admin') {
      abort(403);
    }

    $pageTitle = 'Canned Ticket Fields';
    $ticketsCannedFields = TicketsCannedField::orderBy('subject')->paginate(50);
    $ticketsPriorities = \App\Services\CacheService::getTicketPriorities();
    $ticketsStatuses = \App\Services\CacheService::getTicketStatuses();
    $ticketsTypes = \App\Services\CacheService::getTicketTypes();
    $locations = \App\Services\CacheService::getLocations();
    $users = \App\Services\CacheService::getUsers();
    return view('admin.ticket-canned-fields.index', compact('pageTitle', 'ticketsCannedFields', 'ticketsPriorities', 'ticketsStatuses', 'ticketsTypes', 'locations', 'users'));
  }

  public function store(StoreTicketsCannedFieldRequest $request)
  {
    $user = auth()->user();
    if (!$user || $user->role !== 'super-admin') {
      abort(403);
    }

    $ticketsCannedField = TicketsCannedField::create($request->validated());

    Session::flash('status', 'success');
    Session::flash('title', 'Canned Ticket Fields: ' . $ticketsCannedField->subject);
    Session::flash('message', 'Successfully created');

    return redirect()->route('admin.ticket-canned-fields.index');
  }

  public function canned(Request $request)
  {
    $pageTitle = 'Create New Ticket';
    $ticketsPriorities = \App\Services\CacheService::getTicketPriorities();
    $ticketsStatuses = \App\Services\CacheService::getTicketStatuses();
    $ticketsTypes = \App\Services\CacheService::getTicketTypes();
    $locations = \App\Services\CacheService::getLocations();
    $users = \App\Services\CacheService::getUsers();

    $ticketsCannedField = TicketsCannedField::where('id', $request->subject)->first();

    return view('tickets.create-with-canned-fields', compact('ticketsPriorities', 'ticketsStatuses', 'ticketsTypes', 'locations', 'users', 'ticketsCannedField', 'pageTitle'));
  }

  public function edit(TicketsCannedField $ticketsCannedField)
  {
    $user = auth()->user();
    if (!$user || $user->role !== 'super-admin') {
      abort(403);
    }

    $pageTitle = 'Edit Ticket Canned Fields - ' . $ticketsCannedField->subject;
    $ticketsPriorities = \App\Services\CacheService::getTicketPriorities();
    $ticketsStatuses = \App\Services\CacheService::getTicketStatuses();
    $ticketsTypes = \App\Services\CacheService::getTicketTypes();
    $locations = \App\Services\CacheService::getLocations();
    $users = \App\Services\CacheService::getUsers();
    return view('admin.ticket-canned-fields.edit', compact('ticketsCannedField', 'ticketsPriorities', 'ticketsStatuses', 'ticketsTypes', 'locations', 'users', 'pageTitle'));
  }

  public function update(StoreTicketsCannedFieldRequest $request, TicketsCannedField $ticketsCannedField)
  {
    $user = auth()->user();
    if (!$user || $user->role !== 'super-admin') {
      abort(403);
    }

    $ticketsCannedField->update($request->validated());

    Session::flash('status', 'success');
    Session::flash('title', 'Canned Ticket Fields: ' . $ticketsCannedField->subject);
    Session::flash('message', 'Successfully updated');

    return redirect()->route('admin.ticket-canned-fields.index');
  }
}
