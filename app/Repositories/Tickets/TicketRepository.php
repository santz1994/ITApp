<?php

namespace App\Repositories\Tickets;

use App\Ticket;
use App\TicketsStatus;
use App\TicketsPriority;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class TicketRepository implements TicketRepositoryInterface
{
    protected $model;

    public function __construct(Ticket $ticket)
    {
        $this->model = $ticket;
    }

    public function getAll()
    {
        return $this->model->with([
            'user', 'assignedTo', 'ticket_status', 
            'ticket_priority', 'ticket_type', 'location', 'asset'
        ])->get();
    }

    public function getAllWithFilters(Request $request, $user = null)
    {
        $user = $user ?? Auth::user();
        $query = $this->model->with([
            'user', 'assignedTo', 'ticket_status', 
            'ticket_priority', 'ticket_type', 'location', 'asset'
        ]);

        // Apply role-based filtering
        $query = $this->applyRoleBasedFilters($query, $user);

        // Apply request filters
        if ($request->filled('status')) {
            $query->where('ticket_status_id', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('ticket_priority_id', $request->priority);
        }

        if ($request->filled('assigned_to') && !$user->hasRole('user')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('ticket_code', 'like', '%' . $request->search . '%')
                  ->orWhere('subject', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function getAllPaginated($perPage = 20, $user = null)
    {
        $user = $user ?? Auth::user();
        $query = $this->model->with([
            'user', 'assignedTo', 'ticket_status', 
            'ticket_priority', 'ticket_type', 'location', 'asset'
        ]);

        $query = $this->applyRoleBasedFilters($query, $user);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find($id)
    {
        return $this->model->with([
            'user', 'assignedTo', 'ticket_status', 'ticket_priority', 
            'ticket_type', 'location', 'asset', 'ticket_entries.user'
        ])->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $ticket = $this->find($id);
        $ticket->update($data);
        return $ticket->fresh();
    }

    public function delete($id)
    {
        $ticket = $this->find($id);
        return $ticket->delete();
    }

    public function getByUser($userId)
    {
        return $this->model->with([
            'ticket_status', 'ticket_priority', 'ticket_type'
        ])->where('user_id', $userId)
          ->orderBy('created_at', 'desc')
          ->get();
    }

    public function getByStatus($statusId)
    {
        return $this->model->with([
            'user', 'assignedTo', 'ticket_priority', 'ticket_type'
        ])->where('ticket_status_id', $statusId)
          ->orderBy('created_at', 'desc')
          ->get();
    }

    public function getByPriority($priorityId)
    {
        return $this->model->with([
            'user', 'assignedTo', 'ticket_status', 'ticket_type'
        ])->where('ticket_priority_id', $priorityId)
          ->orderBy('created_at', 'desc')
          ->get();
    }

    public function getAssignedTo($adminId)
    {
        return $this->model->with([
            'user', 'ticket_status', 'ticket_priority', 'ticket_type'
        ])->where('assigned_to', $adminId)
          ->orderBy('created_at', 'desc')
          ->get();
    }

    public function getUnassigned()
    {
        return $this->model->with([
            'user', 'ticket_status', 'ticket_priority', 'ticket_type'
        ])->whereNull('assigned_to')
          ->orderBy('created_at', 'desc')
          ->get();
    }

    public function getTicketStats($user = null)
    {
        $user = $user ?? Auth::user();
        $query = $this->model->query();
        
        if ($user && $user->hasRole('user')) {
            $query->where('user_id', $user->id);
        } elseif ($user && $user->hasRole('admin')) {
            $query->where('assigned_to', $user->id);
        }

        return [
            'total' => $query->count(),
                'open' => $query->whereHas('ticket_status', function($q) {
                    $q->where('status', 'Open');
                })->count(),
                'in_progress' => $query->whereHas('ticket_status', function($q) {
                    $q->where('status', 'In Progress');
                })->count(),
                'closed' => $query->whereHas('ticket_status', function($q) {
                    $q->whereIn('status', ['Closed', 'Resolved']);
                })->count(),
        ];
    }

    public function getRecentTickets($limit = 10, $user = null)
    {
        $user = $user ?? Auth::user();
        $query = $this->model->with([
            'user', 'assignedTo', 'ticket_status', 'ticket_priority'
        ]);

        $query = $this->applyRoleBasedFilters($query, $user);

        return $query->orderBy('created_at', 'desc')->limit($limit)->get();
    }

    public function assignToAdmin($ticketId, $adminId)
    {
        $ticket = $this->find($ticketId);
        $ticket->update(['assigned_to' => $adminId]);
        return $ticket->fresh('assignedTo');
    }

    public function updateStatus($ticketId, $statusId)
    {
        $ticket = $this->find($ticketId);
        $ticket->update(['ticket_status_id' => $statusId]);
        return $ticket->fresh('ticket_status');
    }

    public function addNote($ticketId, $note, $userId)
    {
        $ticket = $this->find($ticketId);
        return $ticket->ticket_entries()->create([
            'user_id' => $userId,
            'entry_type' => 'note',
            'entry' => $note,
        ]);
    }

    public function search($searchTerm, $user = null)
    {
        $user = $user ?? Auth::user();
        $query = $this->model->with([
            'user', 'assignedTo', 'ticket_status', 'ticket_priority', 'ticket_type'
        ]);

        $query = $this->applyRoleBasedFilters($query, $user);

        $query->where(function($q) use ($searchTerm) {
            $q->where('ticket_code', 'like', "%{$searchTerm}%")
              ->orWhere('subject', 'like', "%{$searchTerm}%")
              ->orWhere('description', 'like', "%{$searchTerm}%");
        });

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getTodayTicketCount()
    {
        return $this->model->whereDate('created_at', \Carbon\Carbon::today())->count();
    }

    public function getTicketsNearDeadline($hours = 2)
    {
        return $this->model->nearDeadline($hours)
                    ->with(['user', 'assignedTo', 'ticket_priority'])
                    ->orderBy('sla_due', 'asc')
                    ->get();
    }

    public function getOverdueTickets()
    {
        return $this->model->overdue()
                    ->with(['user', 'assignedTo', 'ticket_priority'])
                    ->orderBy('sla_due', 'asc')
                    ->get();
    }

    protected function applyRoleBasedFilters(Builder $query, $user)
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('user')) {
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('management')) {
            // Management can see all tickets
        } elseif ($user->hasAnyRole(['admin', 'super-admin'])) {
            // Admin and SuperAdmin can see all tickets
        }

        return $query;
    }
}