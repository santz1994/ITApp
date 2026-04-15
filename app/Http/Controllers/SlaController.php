<?php

namespace App\Http\Controllers;

use App\SlaPolicy;
use App\TicketsPriority;
use App\User;
use App\Ticket;
use App\Services\SlaTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SlaController extends Controller
{
    protected $slaService;
    
    public function __construct(SlaTrackingService $slaService)
    {
        $this->middleware('auth');
        $this->slaService = $slaService;
    }
    
    /**
     * Display listing of SLA policies
     */
    public function index()
    {
        $this->authorize('view', SlaPolicy::class);
        
        $policies = SlaPolicy::with(['priority', 'escalateToUser'])
                            ->orderBy('priority_id')
                            ->paginate(15);
        
        return view('sla.index', compact('policies'));
    }
    
    /**
     * Show form to create new SLA policy
     */
    public function create()
    {
        $this->authorize('create', SlaPolicy::class);
        
        $priorities = \App\Services\CacheService::getTicketPriorities();
        $users = User::where('is_active', true)->orderBy('name')->get();
        
        return view('sla.create', compact('priorities', 'users'));
    }
    
    /**
     * Store new SLA policy
     */
    public function store(Request $request)
    {
        $this->authorize('create', SlaPolicy::class);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'response_time' => 'required|integer|min:1',
            'resolution_time' => 'required|integer|min:1',
            'priority_id' => 'required|exists:tickets_priorities,id',
            'business_hours_only' => 'boolean',
            'escalation_time' => 'nullable|integer|min:1',
            'escalate_to_user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }
        
        // Check if policy already exists for this priority
        $existingPolicy = SlaPolicy::where('priority_id', $request->priority_id)
                                   ->where('is_active', true)
                                   ->first();
        
        if ($existingPolicy) {
            return redirect()->back()
                           ->with('warning', 'An active SLA policy already exists for this priority. Please deactivate it first.')
                           ->withInput();
        }
        
        $policy = SlaPolicy::create($request->all());
        
        return redirect()->route('sla.index')
                        ->with('success', "SLA Policy '{$policy->name}' created successfully!");
    }
    
    /**
     * Show form to edit SLA policy
     */
    public function edit($id)
    {
        $policy = SlaPolicy::findOrFail($id);
        $this->authorize('update', $policy);
        
        $priorities = \App\Services\CacheService::getTicketPriorities();
        $users = User::where('is_active', true)->orderBy('name')->get();
        
        return view('sla.edit', compact('policy', 'priorities', 'users'));
    }
    
    /**
     * Update SLA policy
     */
    public function update(Request $request, $id)
    {
        $policy = SlaPolicy::findOrFail($id);
        $this->authorize('update', $policy);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'response_time' => 'required|integer|min:1',
            'resolution_time' => 'required|integer|min:1',
            'priority_id' => 'required|exists:tickets_priorities,id',
            'business_hours_only' => 'boolean',
            'escalation_time' => 'nullable|integer|min:1',
            'escalate_to_user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }
        
        $policy->update($request->all());
        
        return redirect()->route('sla.index')
                        ->with('success', "SLA Policy '{$policy->name}' updated successfully!");
    }
    
    /**
     * Delete SLA policy
     */
    public function destroy($id)
    {
        $policy = SlaPolicy::findOrFail($id);
        $this->authorize('delete', $policy);
        
        $policyName = $policy->name;
        $policy->delete();
        
        return redirect()->route('sla.index')
                        ->with('success', "SLA Policy '{$policyName}' deleted successfully!");
    }
    
    /**
     * Toggle active status
     */
    public function toggleActive($id)
    {
        $policy = SlaPolicy::findOrFail($id);
        $this->authorize('update', $policy);
        
        $policy->is_active = !$policy->is_active;
        $policy->save();
        
        $status = $policy->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('sla.index')
                        ->with('success', "SLA Policy '{$policy->name}' {$status} successfully!");
    }
    
    /**
     * Display SLA dashboard with metrics
     */
    public function dashboard(Request $request)
    {
        $this->authorize('view', SlaPolicy::class);
        
        // Get filter parameters
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $priorityId = $request->input('priority_id');
        $assignedTo = $request->input('assigned_to');
        
        // Build filters
        $filters = [
            'start_date' => Carbon::parse($startDate),
            'end_date' => Carbon::parse($endDate)->endOfDay(),
        ];
        
        if ($priorityId) {
            $filters['priority_id'] = $priorityId;
        }
        
        if ($assignedTo) {
            $filters['assigned_to'] = $assignedTo;
        }
        
        // Get SLA metrics
        $metrics = $this->slaService->getSlaMetrics($filters);
        
        // Get breached tickets
        $breachedTickets = Ticket::with(['user', 'ticket_status', 'ticket_priority', 'assignedTo'])
                                 ->where('created_at', '>=', $filters['start_date'])
                                 ->where('created_at', '<=', $filters['end_date'])
                                 ->whereNotNull('sla_due')
                                 ->where(function($query) {
                                     $query->whereNull('resolved_at')
                                           ->where('sla_due', '<', now());
                                 })
                                 ->orWhere(function($query) use ($filters) {
                                     $query->whereNotNull('resolved_at')
                                           ->whereColumn('resolved_at', '>', 'sla_due')
                                           ->where('created_at', '>=', $filters['start_date'])
                                           ->where('created_at', '<=', $filters['end_date']);
                                 })
                                 ->orderBy('sla_due', 'asc')
                                 ->limit(10)
                                 ->get();
        
        // Get critical tickets (SLA warning)
        $criticalTickets = Ticket::with(['user', 'ticket_status', 'ticket_priority', 'assignedTo'])
                                 ->whereNull('resolved_at')
                                 ->whereNotNull('sla_due')
                                 ->where('sla_due', '>', now())
                                 ->where('sla_due', '<=', now()->addHours(4))
                                 ->orderBy('sla_due', 'asc')
                                 ->limit(10)
                                 ->get();
        
        // Get priorities for filter
        $priorities = \App\Services\CacheService::getTicketPriorities();
        
        // Get users for filter
        $users = User::where('is_active', true)
                    ->orderBy('name')
                    ->get();
        
        // Get active SLA policies for display
        $activePolicies = SlaPolicy::with('priority')
                                   ->where('is_active', true)
                                   ->orderBy('priority_id')
                                   ->get();
        
        return view('sla.dashboard', compact(
            'metrics',
            'breachedTickets',
            'criticalTickets',
            'priorities',
            'users',
            'activePolicies',
            'startDate',
            'endDate',
            'priorityId',
            'assignedTo'
        ));
    }
    
    /**
     * API endpoint: Get SLA status for a ticket
     */
    public function getTicketSlaStatus($ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        $slaStatus = $this->slaService->getSlaStatus($ticket);
        
        return response()->json($slaStatus);
    }
    
    /**
     * API endpoint: Check SLA breach
     */
    public function checkBreach($ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        $breachInfo = $this->slaService->checkSlaBreach($ticket);
        
        return response()->json($breachInfo);
    }
    
    /**
     * API endpoint: Get SLA metrics
     */
    public function getMetrics(Request $request)
    {
        $filters = [];
        
        if ($request->has('start_date')) {
            $filters['start_date'] = Carbon::parse($request->start_date);
        }
        
        if ($request->has('end_date')) {
            $filters['end_date'] = Carbon::parse($request->end_date)->endOfDay();
        }
        
        if ($request->has('priority_id')) {
            $filters['priority_id'] = $request->priority_id;
        }
        
        if ($request->has('assigned_to')) {
            $filters['assigned_to'] = $request->assigned_to;
        }
        
        $metrics = $this->slaService->getSlaMetrics($filters);
        
        return response()->json($metrics);
    }
}
