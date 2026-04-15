<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\AssignTicketRequest;
use App\Services\TicketService;
use App\Ticket;

/**
 * TicketAssignmentController
 * 
 * Handles ticket assignment operations including self-assignment,
 * manual assignment, and force reassignment.
 */
class TicketAssignmentController extends Controller
{
    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->middleware('auth');
        $this->ticketService = $ticketService;
    }

    /**
     * Self-assign ticket to current user
     * 
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\RedirectResponse
     */
    public function selfAssign(Ticket $ticket)
    {
        try {
            $this->ticketService->selfAssignTicket($ticket, auth()->id());
            
            return redirect()->route('tickets.show', $ticket->id)
                           ->with('success', 'Ticket berhasil diambil dan ditugaskan kepada Anda');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengambil ticket: ' . $e->getMessage());
        }
    }

    /**
     * Assign ticket to specific admin (SuperAdmin only)
     * 
     * @param  \App\Http\Requests\Tickets\AssignTicketRequest  $request
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assign(AssignTicketRequest $request, Ticket $ticket)
    {
        try {
            $this->ticketService->assignTicket($ticket, $request->admin_id, 'super_admin');
            
            return redirect()->route('tickets.show', $ticket->id)
                           ->with('success', 'Ticket berhasil ditugaskan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menugaskan ticket: ' . $e->getMessage());
        }
    }

    /**
     * Force assign ticket (SuperAdmin override)
     * 
     * @param  \App\Http\Requests\Tickets\AssignTicketRequest  $request
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceAssign(AssignTicketRequest $request, Ticket $ticket)
    {
        try {
            $this->ticketService->assignTicket($ticket, $request->admin_id, 'super_admin');
            
            return redirect()->route('tickets.show', $ticket->id)
                           ->with('success', 'Ticket berhasil di-reassign');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal reassign ticket: ' . $e->getMessage());
        }
    }
}
