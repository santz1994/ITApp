<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TicketService;
use App\Services\CacheService;
use App\Ticket;
use App\Asset;

/**
 * UserTicketController
 * 
 * Handles user-facing ticket portal for non-admin users.
 * Provides self-service ticket creation, viewing, and response functionality.
 */
class UserTicketController extends Controller
{
    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->middleware('auth');
        $this->ticketService = $ticketService;
    }

    /**
     * Display listing of user's tickets
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function userTickets(Request $request)
    {
        $user = auth()->user();
        $query = Ticket::withRelations()->where('user_id', $user->id);

        // Filter by status if provided (only when non-empty)
        if ($request->filled('status')) {
            $query->where('ticket_status_id', $request->status);
        }

        // Search by ticket code or subject
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('ticket_code', 'like', '%' . $request->search . '%')
                  ->orWhere('subject', 'like', '%' . $request->search . '%');
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(20);
        $statuses = CacheService::getTicketStatuses();
        $pageTitle = 'Tiket Saya';

        return view('tickets.user.index', compact('tickets', 'statuses', 'pageTitle'));
    }

    /**
     * Show user-friendly ticket creation form
     * 
     * @return \Illuminate\View\View
     */
    public function userCreate()
    {
        $user = auth()->user();
        
        // Get user's assets
        $userAssets = Asset::where('assigned_to', $user->id)
                          ->with(['model', 'status'])
                          ->orderBy('asset_tag')
                          ->get();
        
        // Get simplified dropdown data for users
        $ticketTypes = CacheService::getTicketTypes();
        $ticketPriorities = CacheService::getTicketPriorities();
        $locations = CacheService::getLocations();
        $pageTitle = 'Buat Tiket Baru';

        return view('tickets.user.create', compact('userAssets', 'ticketTypes', 'ticketPriorities', 'locations', 'pageTitle'));
    }

    /**
     * Store user-created ticket
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function userStore(Request $request)
    {
        $user = auth()->user();
        
        // Validation rules for user self-service
        $validatedData = $request->validate([
            'ticket_type_id' => 'required|exists:tickets_types,id',
            'ticket_priority_id' => 'required|exists:tickets_priorities,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'asset_id' => 'nullable|exists:assets,id',
            'location_id' => 'nullable|exists:locations,id'
        ]);

        // Ensure user can only assign tickets to their own assets
        if ($request->asset_id) {
            $asset = Asset::where('id', $request->asset_id)
                         ->where('assigned_to', $user->id)
                         ->first();
            if (!$asset) {
                return back()->withErrors(['asset_id' => 'Anda hanya dapat memilih aset yang ditugaskan kepada Anda.'])
                            ->withInput();
            }
        }

        try {
            // Set default values for user-created tickets
            $validatedData['user_id'] = $user->id;
            $validatedData['ticket_status_id'] = 1; // Default to 'Open' status
            $validatedData['location_id'] = $validatedData['location_id'] ?? $user->location_id;
            
            $ticket = $this->ticketService->createTicket($validatedData);
            
            return redirect()->route('tickets.user-show', $ticket->id)
                           ->with('success', 'Tiket berhasil dibuat dengan kode: ' . $ticket->ticket_code);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal membuat tiket: ' . $e->getMessage()])
                         ->withInput();
        }
    }

    /**
     * Show user's specific ticket (read-only for users)
     * 
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\View\View
     */
    public function userShow(Ticket $ticket)
    {
        $user = auth()->user();
        
        // Ensure user can only view their own tickets
        if ($ticket->user_id !== $user->id) {
            abort(403, 'Anda hanya dapat melihat tiket Anda sendiri.');
        }

        $ticket->load(['user', 'assignedTo', 'ticket_status', 'ticket_priority', 'ticket_type', 'location', 'asset.model', 'ticket_entries']);
        $pageTitle = 'Detail Tiket - ' . $ticket->ticket_code;
        
        // Get ticket entries for timeline
        $ticketEntries = $ticket->ticket_entries()->with('user')->orderBy('created_at', 'desc')->get();
        
        return view('tickets.user.show', compact('ticket', 'pageTitle', 'ticketEntries'));
    }

    /**
     * Add technician response to ticket
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addResponse(Request $request, Ticket $ticket)
    {
        $request->validate([
            'response' => 'required|string',
            'status_change' => 'nullable|in:In Progress,Pending,Resolved'
        ]);

        try {
            $userId = auth()->id();

            // Add the response
            $this->ticketService->addFirstResponse($ticket, $userId, $request->response);

            // Update status if requested
            if ($request->status_change) {
                $this->ticketService->updateTicketStatus($ticket, $request->status_change, $userId);
            }

            return redirect()->route('tickets.show', $ticket->id)
                           ->with('success', 'Respons berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menambahkan respons: ' . $e->getMessage()]);
        }
    }
}
