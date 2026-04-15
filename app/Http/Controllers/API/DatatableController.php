<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Asset;
use App\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatatableController extends Controller
{
    /**
     * Get assets for server-side DataTables processing
     * 
     * Expected query parameters (DataTables standard):
     * - draw: draw counter (for tracking responses)
     * - start: start row index
     * - length: number of rows to return
     * - search[value]: search term
     * - order[0][column]: column index to sort by
     * - order[0][dir]: sort direction (asc/desc)
     * - columns[*][search][value]: individual column search values
     */
    public function assets(Request $request)
    {
        try {
            // Get DataTables parameters
            $draw = $request->input('draw', 1);
            $start = $request->input('start', 0);
            $length = $request->input('length', 25);
            $searchValue = $request->input('search.value', '');
            $sortColumn = $request->input('order.0.column', 0);
            $sortDir = $request->input('order.0.dir', 'asc');

            // Build base query with relationships
            $query = Asset::with(['model', 'location', 'assignedUser', 'status'])
                         ->select('assets.*');

            // Apply global search filter
            if ($searchValue) {
                $query->where(function($q) use ($searchValue) {
                    $q->where('name', 'like', "%{$searchValue}%")
                      ->orWhere('asset_tag', 'like', "%{$searchValue}%")
                      ->orWhere('serial_number', 'like', "%{$searchValue}%");
                });
            }

            // Apply column-specific filters from request
            if ($request->has('columns')) {
                $columns = $request->input('columns', []);
                
                if (isset($columns[2]['search']['value']) && $columns[2]['search']['value']) {
                    $query->where('asset_type_id', $columns[2]['search']['value']);
                }
                
                if (isset($columns[3]['search']['value']) && $columns[3]['search']['value']) {
                    $query->where('location_id', $columns[3]['search']['value']);
                }
                
                if (isset($columns[4]['search']['value']) && $columns[4]['search']['value']) {
                    $query->where('status_id', $columns[4]['search']['value']);
                }
                
                if (isset($columns[5]['search']['value']) && $columns[5]['search']['value']) {
                    $query->where('assigned_to', $columns[5]['search']['value']);
                }
            }

            // Get total count before filtering
            $totalRecords = Asset::count();
            
            // Get filtered count
            $filteredRecords = $query->count();

            // Apply sorting
            $sortColumns = ['asset_tag', 'name', 'model_name', 'location', 'status', 'assigned_user', 'purchase_date'];
            $columnName = $sortColumns[$sortColumn] ?? 'assets.asset_tag';
            
            $query->orderBy($columnName, $sortDir);

            // Apply pagination
            $assets = $query->skip($start)->take($length)->get();

            // Format data for DataTables
            $data = $assets->map(function($asset) {
                return [
                    'id' => $asset->id,
                    'asset_tag' => $asset->asset_tag,
                    'name' => $asset->name,
                    'model' => $asset->model?->asset_model ?? 'N/A',
                    'location' => $asset->location?->location_name ?? 'N/A',
                    'status' => $asset->status?->name ?? 'N/A',
                    'assigned_to' => $asset->assignedUser?->name ?? 'Unassigned',
                    'purchase_date' => $asset->purchase_date ? date('Y-m-d', strtotime($asset->purchase_date)) : 'N/A',
                    'action' => route('assets.show', $asset->id)
                ];
            });

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch assets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tickets for server-side DataTables processing
     */
    public function tickets(Request $request)
    {
        try {
            // Get DataTables parameters
            $draw = $request->input('draw', 1);
            $start = $request->input('start', 0);
            $length = $request->input('length', 25);
            $searchValue = $request->input('search.value', '');
            $sortColumn = $request->input('order.0.column', 0);
            $sortDir = $request->input('order.0.dir', 'asc');

            // Apply role-based filtering
            $user = auth()->user();
            $query = Ticket::with(['user', 'assignedTo', 'ticket_status', 'ticket_priority', 'asset'])
                           ->select('tickets.*');

            // Role-based filters
            if ($user->hasRole(['user'])) {
                // Users can only see their own tickets
                $query->where('user_id', $user->id);
            } elseif ($user->hasRole(['admin'])) {
                // Admins can see tickets assigned to them or created by others
                $query->where(function($q) use ($user) {
                    $q->where('assigned_to', $user->id)
                      ->orWhere('user_id', $user->id);
                });
            }
            // Super-admins see all tickets (no filter)

            // Apply global search filter
            if ($searchValue) {
                $query->where(function($q) use ($searchValue) {
                    $q->where('ticket_code', 'like', "%{$searchValue}%")
                      ->orWhere('subject', 'like', "%{$searchValue}%");
                });
            }

            // Apply column-specific filters
            if ($request->has('columns')) {
                $columns = $request->input('columns', []);
                
                if (isset($columns[1]['search']['value']) && $columns[1]['search']['value']) {
                    $query->where('ticket_status_id', $columns[1]['search']['value']);
                }
                
                if (isset($columns[2]['search']['value']) && $columns[2]['search']['value']) {
                    $query->where('ticket_priority_id', $columns[2]['search']['value']);
                }
                
                if (isset($columns[3]['search']['value']) && $columns[3]['search']['value']) {
                    $query->where('assigned_to', $columns[3]['search']['value']);
                }
            }

            // Get total count before filtering
            $totalRecords = Ticket::count();
            
            // Get filtered count
            $filteredRecords = $query->count();

            // Apply sorting
            $sortColumns = ['ticket_code', 'status', 'priority', 'assigned_user', 'created_at', 'updated_at'];
            $columnName = $sortColumns[$sortColumn] ?? 'tickets.created_at';
            
            if ($columnName === 'status') {
                $query->leftJoin('tickets_statuses', 'tickets.ticket_status_id', '=', 'tickets_statuses.id')
                      ->orderBy('tickets_statuses.status', $sortDir)
                      ->select('tickets.*');
            } elseif ($columnName === 'priority') {
                $query->leftJoin('tickets_priorities', 'tickets.ticket_priority_id', '=', 'tickets_priorities.id')
                      ->orderBy('tickets_priorities.priority', $sortDir)
                      ->select('tickets.*');
            } else {
                $query->orderBy($columnName, $sortDir);
            }

            // Apply pagination
            $tickets = $query->skip($start)->take($length)->get();

            // Format data for DataTables
            $data = $tickets->map(function($ticket) {
                return [
                    'id' => $ticket->id,
                    'ticket_code' => $ticket->ticket_code,
                    'status' => $ticket->ticket_status?->status ?? 'N/A',
                    'priority' => $ticket->ticket_priority?->priority ?? 'N/A',
                    'assigned_to' => $ticket->assignedTo?->name ?? 'Unassigned',
                    'subject' => $ticket->subject,
                    'created_at' => $ticket->created_at->format('Y-m-d H:i'),
                    'action' => route('tickets.show', $ticket->id)
                ];
            });

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch tickets: ' . $e->getMessage()
            ], 500);
        }
    }
}
