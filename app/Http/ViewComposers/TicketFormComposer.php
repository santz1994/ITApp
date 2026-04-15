<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\TicketsPriority;
use App\TicketsStatus;
use App\TicketsType;
use App\User;
use App\Location;
use App\Asset;
use Illuminate\Support\Facades\Cache;

class TicketFormComposer
{
    /**
     * Bind ticket-specific data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with([
            'ticketsPriorities' => Cache::remember('ticket_priorities_objects', 3600, function () {
                return TicketsPriority::select('id', 'priority')->orderBy('priority')->get();
            }),
            'ticketsStatuses' => Cache::remember('ticket_statuses_objects', 3600, function () {
                return TicketsStatus::select('id', 'status')->orderBy('status')->get();
            }),
            'ticketsTypes' => Cache::remember('ticket_types_objects', 3600, function () {
                return TicketsType::select('id', 'type')->orderBy('type')->get();
            }),
            'users' => Cache::remember('users_objects', 1800, function () {
                return User::select('id', 'name')->orderBy('name')->get();
            }),
            'locations' => Cache::remember('locations_objects', 3600, function () {
                return Location::select('id', 'location_name')->orderBy('location_name')->get();
            }),
            'assets' => Cache::remember('assets_for_tickets_objects', 1800, function () {
                return Asset::with(['model', 'assignedTo', 'division'])->orderBy('asset_tag')->get();
            }),
            'assignableUsers' => Cache::remember('assignable_users_objects', 1800, function () {
                // Only allow Ridwan and Idol as ticket agents
                return User::select('id', 'name', 'email')
                    ->whereIn('name', ['Ridwan', 'Idol'])
                    ->orderBy('name')
                    ->get();
            }),
        ]);
    }
}