<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ticket;
use App\User;
use App\TicketsStatus;
use App\TicketsPriority;
use App\TicketsType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BulkOperationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        
        // Log every request to bulk operations for debugging
        Log::info('BulkOperationController instantiated', [
            'route' => \Request::route()?->getName(),
            'path' => \Request::path(),
            'method' => \Request::method(),
        ]);
    }

    /**
     * Get bulk operation options (users, statuses, priorities, types)
     * Used to populate dropdown menus in bulk operations modals
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBulkOptions()
    {
        try {
            /** @var \App\User $user */
            $user = Auth::user();
            
            // Get users - only show Idol and Ridwan for bulk assign operations
            $users = User::select('id', 'name', 'email')
                ->whereIn('name', ['Ridwan', 'Idol'])
                ->orderBy('name')
                ->get()
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email
                    ];
                });

            // Get statuses - use the 'name' accessor which returns 'status'
            $statuses = TicketsStatus::orderBy('status')
                ->get()
                ->map(function($status) {
                    return [
                        'id' => $status->id,
                        'name' => $status->name  // This uses the getNameAttribute() accessor
                    ];
                });

            // Get priorities - use the 'name' accessor which returns 'priority'
            $priorities = TicketsPriority::orderBy('priority')
                ->get()
                ->map(function($priority) {
                    return [
                        'id' => $priority->id,
                        'name' => $priority->name  // This uses the getNameAttribute() accessor
                    ];
                });

            // Get types/categories - use the 'name' accessor which returns 'type'
            $types = TicketsType::orderBy('type')
                ->get()
                ->map(function($type) {
                    return [
                        'id' => $type->id,
                        'name' => $type->name  // This uses the getNameAttribute() accessor
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $users,
                    'statuses' => $statuses,
                    'priorities' => $priorities,
                    'types' => $types
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get bulk options: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load bulk operation options'
            ], 500);
        }
    }

    /**
     * Bulk assign tickets to a user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'exists:tickets,id',
            'assigned_to' => 'required|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $ticketIds = $request->ticket_ids;
            $assignedTo = $request->assigned_to;
            /** @var \App\User $user */
            $user = Auth::user();

            // Check authorization for each ticket with eager loading
            $tickets = Ticket::withRelations()->whereIn('id', $ticketIds)->get();
            
            foreach ($tickets as $ticket) {
                // Only allow if user can update the ticket
                if (!$user->hasRole('super-admin') && !$user->hasRole('admin') && $ticket->assigned_to != $user->id) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "You don't have permission to modify ticket #{$ticket->id}"
                    ], 403);
                }
            }

            // Perform bulk assignment
            $updatedCount = Ticket::whereIn('id', $ticketIds)->update([
                'assigned_to' => $assignedTo,
                'updated_at' => now()
            ]);

            // Get assigned user and send notifications
            $assignedUser = User::find($assignedTo);
            if (!$assignedUser) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Assigned user not found'
                ], 404);
            }

            // Send assignment notifications to the assigned user for each ticket
            $assignedTickets = Ticket::withRelations()->whereIn('id', $ticketIds)->get();
            foreach ($assignedTickets as $ticket) {
                try {
                    $assignedUser->notify(new \App\Notifications\TicketAssigned($ticket, $user));
                } catch (\Exception $e) {
                    Log::warning("Failed to send assignment notification for ticket {$ticket->id}: " . $e->getMessage());
                }
            }

            DB::commit();

            Log::info("Bulk assign: User {$user->id} assigned {$updatedCount} tickets to user {$assignedTo}");

            return response()->json([
                'success' => true,
                'message' => "Successfully assigned {$updatedCount} ticket(s) to {$assignedUser->name}",
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk assign error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while assigning tickets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update ticket status
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpdateStatus(Request $request)
    {
        try {
            // Debug logging
            Log::info('Bulk update status request', [
                'all_data' => $request->all(),
                'ticket_ids' => $request->ticket_ids,
                'ticket_ids_types' => array_map('gettype', $request->ticket_ids ?? []),
                'status_id' => $request->status_id
            ]);

            // Convert string IDs to integers
            if ($request->has('ticket_ids') && is_array($request->ticket_ids)) {
                $request->merge([
                    'ticket_ids' => array_map('intval', $request->ticket_ids)
                ]);
            }

            $request->validate([
                'ticket_ids' => 'required|array|min:1',
                'ticket_ids.*' => 'exists:tickets,id',
                'status_id' => 'required|exists:tickets_statuses,id',
            ]);

            DB::beginTransaction();

            $ticketIds = $request->ticket_ids;
            $statusId = $request->status_id;
            /** @var \App\User $user */
            $user = Auth::user();

            // Check authorization with eager loading
            $tickets = Ticket::withRelations()->whereIn('id', $ticketIds)->get();
            
            if ($tickets->isEmpty()) {
                Log::warning('No tickets found for bulk status update', ['ticket_ids' => $ticketIds]);
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No tickets found with the provided IDs'
                ], 404);
            }
            
            foreach ($tickets as $ticket) {
                if (!$user->hasRole('super-admin') && !$user->hasRole('admin') && $ticket->assigned_to != $user->id) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "You don't have permission to modify ticket #{$ticket->id}"
                    ], 403);
                }
            }

            // Get status name
            $status = TicketsStatus::find($statusId);
            if (!$status) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Status not found'
                ], 404);
            }

            // Check if status is "Resolved" or "Closed" to set resolved_at
            $updateData = [
                'ticket_status_id' => $statusId,
                'updated_at' => now()
            ];

            if (in_array(strtolower($status->name), ['resolved', 'closed'])) {
                $updateData['resolved_at'] = now();
            }

            // Perform bulk update
            $updatedCount = Ticket::whereIn('id', $ticketIds)->update($updateData);

            DB::commit();

            Log::info("Bulk status update: User {$user->id} updated {$updatedCount} tickets to status {$statusId}");

            return response()->json([
                'success' => true,
                'message' => "Successfully updated status of {$updatedCount} ticket(s) to {$status->name}",
                'updated_count' => $updatedCount
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::warning('Validation error in bulk status update: ' . json_encode($e->errors()));
            
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . implode(', ', array_map(fn($errors) => implode(', ', $errors), $e->errors())),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk status update error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating ticket status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update ticket priority
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpdatePriority(Request $request)
    {
        $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'exists:tickets,id',
            'priority_id' => 'required|exists:tickets_priorities,id',
        ]);

        try {
            DB::beginTransaction();

            $ticketIds = $request->ticket_ids;
            $priorityId = $request->priority_id;
            /** @var \App\User $user */
            $user = Auth::user();

            // Check authorization with eager loading
            $tickets = Ticket::withRelations()->whereIn('id', $ticketIds)->get();
            
            foreach ($tickets as $ticket) {
                if (!$user->hasRole('super-admin') && !$user->hasRole('admin') && $ticket->assigned_to != $user->id) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "You don't have permission to modify ticket #{$ticket->id}"
                    ], 403);
                }
            }

            // Perform bulk update
            $updatedCount = Ticket::whereIn('id', $ticketIds)->update([
                'priority_id' => $priorityId,
                'updated_at' => now()
            ]);

            // Get priority name
            $priority = TicketsPriority::find($priorityId);
            if (!$priority) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Priority not found'
                ], 404);
            }

            DB::commit();

            Log::info("Bulk priority update: User {$user->id} updated {$updatedCount} tickets to priority {$priorityId}");

            return response()->json([
                'success' => true,
                'message' => "Successfully updated priority of {$updatedCount} ticket(s) to {$priority->name}",
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk priority update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating ticket priority: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update ticket category/type
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpdateCategory(Request $request)
    {
        $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'exists:tickets,id',
            'type_id' => 'required|exists:tickets_types,id',
        ]);

        try {
            DB::beginTransaction();

            $ticketIds = $request->ticket_ids;
            $typeId = $request->type_id;
            /** @var \App\User $user */
            $user = Auth::user();

            // Check authorization with eager loading
            $tickets = Ticket::withRelations()->whereIn('id', $ticketIds)->get();
            
            foreach ($tickets as $ticket) {
                if (!$user->hasRole('super-admin') && !$user->hasRole('admin') && $ticket->assigned_to != $user->id) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "You don't have permission to modify ticket #{$ticket->id}"
                    ], 403);
                }
            }

            // Perform bulk update
            $updatedCount = Ticket::whereIn('id', $ticketIds)->update([
                'type_id' => $typeId,
                'updated_at' => now()
            ]);

            // Get category name
            $type = TicketsType::find($typeId);
            if (!$type) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            DB::commit();

            Log::info("Bulk category update: User {$user->id} updated {$updatedCount} tickets to category {$typeId}");

            return response()->json([
                'success' => true,
                'message' => "Successfully updated category of {$updatedCount} ticket(s) to {$type->name}",
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk category update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating ticket category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete tickets
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'exists:tickets,id',
        ]);

        try {
            DB::beginTransaction();

            $ticketIds = $request->ticket_ids;
            /** @var \App\User $user */
            $user = Auth::user();

            // Only super-admin and admin can bulk delete
            if (!$user->hasRole('super-admin') && !$user->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete tickets'
                ], 403);
            }

            // Get tickets for logging with eager loading
            $tickets = Ticket::withRelations()->whereIn('id', $ticketIds)->get();

            // Soft delete tickets (if using soft deletes)
            $deletedCount = Ticket::whereIn('id', $ticketIds)->delete();

            DB::commit();

            Log::warning("Bulk delete: User {$user->id} deleted {$deletedCount} tickets: " . implode(', ', $ticketIds));

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} ticket(s)",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk delete error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting tickets: ' . $e->getMessage()
            ], 500);
        }
    }
}
