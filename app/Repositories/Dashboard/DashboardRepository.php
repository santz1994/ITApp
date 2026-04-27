<?php

namespace App\Repositories\Dashboard;

use App\Ticket;
use Illuminate\Support\Collection;

class DashboardRepository
{
    /**
     * Get ticket counts for integrated dashboard cards.
     *
     * Open ticket count intentionally follows existing legacy rule:
     * any ticket_status_id except 3 (resolved sentinel).
     *
     * @return array<string, int>
     */
    public function getTicketStats(): array
    {
        return [
            'open_tickets' => Ticket::query()
                ->where('ticket_status_id', '!=', 3)
                ->count(),
            'overdue_tickets' => Ticket::query()
                ->where('sla_due', '<', now('Asia/Jakarta'))
                ->count(),
        ];
    }

    /**
     * Get recent tickets with relations for dashboard table.
     */
    public function getRecentTickets(int $limit = 10): Collection
    {
        return Ticket::query()
            ->with(['user', 'ticket_status', 'ticket_priority', 'location'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}