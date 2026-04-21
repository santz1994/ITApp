<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CreateTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Services\TicketService;
use App\Services\CacheService;
use App\Ticket;
use App\Asset;
use App\User;
use App\Location;
use App\TicketsPriority;
use App\TicketsType;
use App\TicketsStatus;
use App\Traits\RoleBasedAccessTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

use App\Http\Controllers\Controller as BaseController;

/**
 * TicketController
 * 
 * Handles core CRUD operations for tickets.
 * Specialized operations moved to:
 * - TicketTimerController (time tracking)
 * - TicketAssignmentController (assignment operations)
 * - TicketStatusController (status updates)
 * - UserTicketController (user self-service portal)
 */
class TicketController extends BaseController
{
    use RoleBasedAccessTrait;
    
    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        // Removed parent::__construct() since parent doesn't have a constructor
        if (method_exists($this, 'middleware')) {
            $this->middleware('auth');
        }
        $this->ticketService = $ticketService;
    }

    /**
     * Display a listing of tickets
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Ticket::withRelations();

        // Role-based filtering using trait method
        $query = $this->applyRoleBasedFilters($query, $user);

        // Filter by status (only when a non-empty value is provided)
        if ($request->filled('status')) {
            $query->where('ticket_status_id', $request->status);
        }

        // Filter by priority (only when a non-empty value is provided)
        if ($request->filled('priority')) {
            $query->where('ticket_priority_id', $request->priority);
        }

        // Filter by assigned admin (only for management, admin, super-admin)  
        if ($request->filled('assigned_to') && !$this->hasRole('user')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by asset
        if ($request->filled('asset_id')) {
            $query->where('asset_id', $request->asset_id);
        }

        // Search by ticket code or subject
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('ticket_code', 'like', '%' . $request->search . '%')
                  ->orWhere('subject', 'like', '%' . $request->search . '%');
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get filter options using cache
        $statuses = CacheService::getTicketStatuses();
        $priorities = CacheService::getTicketPriorities();
        $admins = User::admins()->orderBy('name')->get();
        $assets = Asset::select('assets.id', 'assets.asset_tag', 'asset_models.asset_model as model_name')
                      ->leftJoin('asset_models', 'assets.model_id', '=', 'asset_models.id')
                      ->orderBy('assets.asset_tag')
                      ->get();
        $pageTitle = 'Ticket Management';

        // Calculate ticket statistics
        $baseQuery = Ticket::query();
        $baseQuery = $this->applyRoleBasedFilters($baseQuery, $user);
        
        // Get status IDs
        $openStatusId = TicketsStatus::where('status', 'Open')->value('id');
        $pendingStatusId = TicketsStatus::where('status', 'Pending')->value('id');
        $resolvedStatusIds = TicketsStatus::whereIn('status', ['Resolved', 'Closed'])->pluck('id');
        
        // Count open tickets (Open status only)
        $openTickets = (clone $baseQuery)
            ->where('ticket_status_id', $openStatusId)
            ->count();
        
        // Count resolved tickets (Resolved and Closed)
        $resolvedTickets = (clone $baseQuery)
            ->whereIn('ticket_status_id', $resolvedStatusIds)
            ->count();
        
        // Count overdue tickets (SLA due date passed and not resolved/closed)
        $overdueTickets = (clone $baseQuery)
            ->where('sla_due', '<', now())
            ->whereNotIn('ticket_status_id', $resolvedStatusIds)
            ->count();

        return view('tickets.index', compact('tickets', 'statuses', 'priorities', 'admins', 'assets', 'pageTitle', 'openTickets', 'resolvedTickets', 'overdueTickets'));
    }

    /**
     * Show the form for creating a new ticket
     */
    public function create(Request $request): View
    {
        // Get dropdown data with correct variable names expected by the view using cache
        // Only show Ridwan and Idol as agents for ticket assignment
        $users = User::select('id', 'name')
                    ->whereIn('name', ['Ridwan', 'Idol'])
                    ->orderBy('name')
                    ->get();
        
        $locations = CacheService::getLocations();
        $ticketsStatuses = CacheService::getTicketStatuses();
        $ticketsTypes = CacheService::getTicketTypes();
        $ticketsPriorities = CacheService::getTicketPriorities();
        $assets = Asset::with(['model', 'assignedTo', 'division'])->orderBy('asset_tag')->get();
        $pageTitle = 'Create New Ticket';
        // Provide canned fields so the view can render the right-hand column
        $ticketsCannedFields = \App\TicketsCannedField::all();
        
        // Pre-select asset if asset_id is passed in query string
        $preselectedAssetId = $request->query('asset_id');

        return view('tickets.create', compact('users', 'locations', 'ticketsStatuses', 'ticketsTypes', 
                                            'ticketsPriorities', 'assets', 'pageTitle', 'ticketsCannedFields', 'preselectedAssetId'));
    }

    /**
     * Show the form for creating a ticket with pre-selected asset
     */
    public function createWithAsset(Request $request): View
    {
        $asset = null;
        if ($request->has('asset_id')) {
            $asset = Asset::find($request->asset_id);
        }

        // Dropdown data is provided by TicketFormComposer
        $assets = Asset::where('assigned_to', auth()->id())->get();

        return view('tickets.create-with-asset', compact('assets', 'asset'));
    }

    /**
     * Store a newly created ticket
     */
    public function store(CreateTicketRequest $request): RedirectResponse
    {
        try {
            $ticket = $this->ticketService->createTicket($request->validated());
            
            return redirect()->route('tickets.show', $ticket->id)
                           ->with('success', 'Ticket berhasil dibuat dengan kode: ' . $ticket->ticket_code);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal membuat ticket: ' . $e->getMessage()])
                         ->withInput();
        }
    }

    /**
     * Display the specified ticket
     */
    public function show(Ticket $ticket): View
    {
        // Authorization using Policy
        $this->authorize('view', $ticket);
        
        $ticket->load([
            'user', 
            'assignedTo', 
            'ticket_status', 
            'ticket_priority', 
            'ticket_type', 
            'location', 
            'asset',
            'assets.model',
            'assets.status',
            'assets.location',
            'assets.movement.location',
            'assets.assignedTo',
            'comments.user',
            'history.changedByUser'
        ]);
        $pageTitle = 'Ticket Details - ' . $ticket->ticket_code;

        // Get ticket entries for the view (the view expects this variable)
        $ticketEntries = $ticket->comments;

        // Dropdown data is provided by TicketFormComposer
        return view('tickets.show', compact('ticket', 'pageTitle', 'ticketEntries'));
    }

    /**
     * Show the form for editing the specified ticket
     */
    public function edit(Ticket $ticket): View
    {
        // Authorization using Policy
        $this->authorize('update', $ticket);
        
        $user = auth()->user();
        
        Log::info('Accessing ticket edit', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id
        ]);

        $ticket->load(['user', 'assignedTo', 'ticket_status', 'ticket_priority', 'ticket_type', 'location', 'asset', 'assets']);
        
        // Get dropdown data for the edit form
        // Note: Most dropdown data is provided by TicketFormComposer
        $assets = Asset::select('assets.id', 'assets.asset_tag', 'asset_models.asset_model as model_name')
                      ->leftJoin('asset_models', 'assets.model_id', '=', 'asset_models.id')
                      ->orderBy('assets.asset_tag')
                      ->get();
        
        return view('tickets.edit', compact('ticket', 'assets'));
    }

    /**
     * Update the specified ticket in storage
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        // Authorization using Policy
        $this->authorize('update', $ticket);
        
        $user = auth()->user();

        try {
            // Check field-level permissions for restricted fields
            $this->authorizeFieldChanges($user, $ticket, $request);

            Log::info('Attempting to update ticket', [
                'ticket_id' => $ticket->id,
                'validated_data' => $request->validated(),
                'user_id' => $user->id
            ]);
            
            $ticket->update($request->validated());

            // Sync assets if provided (support asset_ids from multi-select)
            try {
                if ($request->filled('asset_ids')) {
                    $ticket->assets()->sync($request->input('asset_ids', []));
                } elseif ($request->filled('asset_id')) {
                    $ticket->assets()->syncWithoutDetaching([$request->input('asset_id')]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to sync ticket assets during web update: ' . $e->getMessage());
            }
            
            Log::info('Ticket updated successfully', ['ticket_id' => $ticket->id]);
            
            return redirect()->route('tickets.show', $ticket)
                           ->with('success', 'Ticket updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update ticket', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()
                        ->with('error', 'Failed to update ticket: ' . $e->getMessage());
        }
    }

    /**
     * Authorize field-level changes based on user role and ticket ownership
     */
    private function authorizeFieldChanges($user, Ticket $ticket, UpdateTicketRequest $request)
    {
        // Super admins can change any field
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Check if user is trying to change restricted fields
        $restrictedFields = ['ticket_priority_id', 'ticket_status_id', 'assigned_to', 'ticket_type_id'];
        
        foreach ($restrictedFields as $field) {
            if ($request->filled($field)) {
                // For non-admin users, only allow changes on their own tickets or tickets assigned to them
                if (!($ticket->user_id === $user->id || $ticket->assigned_to === $user->id)) {
                    abort(403, "You do not have permission to modify {$field}");
                }
            }
        }

        return true;
    }

    /**
     * Remove the specified ticket
     */
    public function destroy(Ticket $ticket): RedirectResponse
    {
        // Authorization using Policy
        $this->authorize('delete', $ticket);

        try {
            $ticket->delete();
            return redirect()->route('tickets.index')->with('success', 'Ticket deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete ticket: ' . $e->getMessage());
        }
    }
    
    /**
     * Mark ticket as resolved (for assigned agent)
     */
    public function resolve(Ticket $ticket): RedirectResponse
    {
        $user = auth()->user();
        
        // Check authorization: assigned agent or super-admin
        if ($ticket->assigned_to !== $user->id && !$user->hasRole('super-admin')) {
            abort(403, 'Unauthorized to resolve this ticket');
        }
        
        try {
            $ticket->update([
                'resolved_at' => now(),
                'ticket_status_id' => TicketsStatus::where('name', 'Resolved')->value('id')
            ]);
            
            return redirect()->route('tickets.show', $ticket)
                           ->with('success', 'Ticket has been marked as resolved');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to resolve ticket: ' . $e->getMessage());
        }
    }
    
    /**
     * Reopen resolved ticket (super-admin only)
     */
    public function unresolve(Ticket $ticket): RedirectResponse
    {
        $user = auth()->user();
        
        // Only super-admin can unresolve
        if (!$user->hasRole('super-admin')) {
            abort(403, 'Only super-admin can reopen tickets');
        }
        
        try {
            $ticket->update([
                'resolved_at' => null,
                'ticket_status_id' => TicketsStatus::where('name', 'Open')->value('id')
            ]);
            
            return redirect()->route('tickets.show', $ticket)
                           ->with('success', 'Ticket has been reopened');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reopen ticket: ' . $e->getMessage());
        }
    }

    /**
     * Show unassigned tickets for admin self-assignment
     */
    public function unassigned(): View
    {
        $tickets = $this->ticketService->getUnassignedTickets();
        
        return view('tickets.unassigned', compact('tickets'));
    }

    /**
     * Show overdue tickets
     */
    public function overdue(): View
    {
        $tickets = $this->ticketService->getOverdueTickets();
        
        return view('tickets.overdue', compact('tickets'));
    }

    /**
     * Export tickets to Excel
     */
    public function export(): mixed
    {
        try {
            $excel = app(\Maatwebsite\Excel\Excel::class);
            return $excel->download(new \App\Exports\TicketsExport, 'tickets_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Export failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Print ticket details to PDF
     */
    public function print($id): mixed
    {
        $ticket = Ticket::with(['user', 'assignedTo', 'location', 'asset', 'ticket_status', 'ticket_priority', 'ticket_type', 'ticket_entries'])
                       ->findOrFail($id);

        // Authorization using Policy
        $this->authorize('view', $ticket);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('tickets.print', compact('ticket'));

        return $pdf->stream('ticket_' . $ticket->ticket_code . '.pdf');
    }
}
