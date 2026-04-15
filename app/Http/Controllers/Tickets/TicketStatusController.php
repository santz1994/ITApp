<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Tickets\CompleteTicketRequest;
use App\Services\TicketService;
use App\Ticket;

/**
 * TicketStatusController
 * 
 * Handles ticket status changes and completion operations.
 * Manages status updates, ticket completion, and resolution recording.
 */
class TicketStatusController extends Controller
{
    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->middleware('auth');
        $this->ticketService = $ticketService;
    }

    /**
     * Update ticket status with notes
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        try {
            $this->ticketService->updateTicketStatus(
                $ticket, 
                $request->status, 
                auth()->id(), 
                $request->notes
            );

            return redirect()->route('tickets.show', $ticket->id)
                           ->with('success', 'Status tiket berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal memperbarui status: ' . $e->getMessage()]);
        }
    }

    /**
     * Complete ticket
     * 
     * @param  \App\Http\Requests\Tickets\CompleteTicketRequest  $request
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete(CompleteTicketRequest $request, Ticket $ticket)
    {
        try {
            $this->ticketService->completeTicket($ticket, $request->resolution);
            
            return redirect()->route('tickets.show', $ticket->id)
                           ->with('success', 'Ticket berhasil diselesaikan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyelesaikan ticket: ' . $e->getMessage());
        }
    }

    /**
     * Complete ticket with resolution
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\RedirectResponse
     */
    public function completeWithResolution(Request $request, Ticket $ticket)
    {
        $request->validate([
            'resolution' => 'required|string|min:10'
        ]);

        try {
            $this->ticketService->closeTicket($ticket, $request->resolution, auth()->id());

            return redirect()->route('tickets.show', $ticket->id)
                           ->with('success', 'Tiket berhasil diselesaikan');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menyelesaikan tiket: ' . $e->getMessage()]);
        }
    }
}
