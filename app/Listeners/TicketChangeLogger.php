<?php

namespace App\Listeners;

use App\TicketHistory;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * TicketChangeLogger
 * 
 * Automatically logs ticket changes to the ticket_history table.
 * Implements audit trail for compliance, SLA tracking, and debugging.
 * 
 * Usage:
 * - Register in EventServiceProvider as listener for model events
 * - Or call directly from controller/observer
 */
class TicketChangeLogger
{
    /**
     * Log generic ticket change - core method used by all specific loggers
     * 
     * @param int $ticketId Ticket ID
     * @param string $fieldName Field that changed
     * @param mixed $oldValue Previous value
     * @param mixed $newValue New value
     * @param int|null $changedByUserId User ID who made the change
     * @param string $changeType Type of change (status_change, priority_change, assignment, escalation, resolution, update)
     * @param string|null $reason Optional reason/description
     * @return TicketHistory
     */
    public static function logChange($ticketId, $fieldName, $oldValue, $newValue, $changedByUserId = null, $changeType = 'update', $reason = null)
    {
        return TicketHistory::create([
            'ticket_id' => $ticketId,
            'field_changed' => $fieldName,
            'old_value' => $oldValue !== null ? (string)$oldValue : null,
            'new_value' => $newValue !== null ? (string)$newValue : null,
            'changed_by_user_id' => $changedByUserId,
            'changed_at' => now(),
            'change_type' => $changeType,
            'event_type' => $changeType, // Add event_type (required column)
            'reason' => $reason,
        ]);
    }

    /**
     * Log ticket status change
     * 
     * @param $ticket Ticket model instance
     * @param $oldStatus string|int Old status ID or name
     * @param $newStatus string|int New status ID or name
     * @param $reason string Optional reason for the change
     * @return TicketHistory
     */
    public static function logStatusChange($ticket, $oldStatus, $newStatus, $reason = null)
    {
        return self::logChange(
            $ticket->id,
            'ticket_status_id',
            $oldStatus,
            $newStatus,
            auth()->id() ?? null,
            'status_change',
            $reason ?? 'Status updated'
        );
    }

    /**
     * Log ticket priority change
     */
    public static function logPriorityChange($ticket, $oldPriority, $newPriority, $reason = null)
    {
        return self::logChange(
            $ticket->id,
            'ticket_priority_id',
            $oldPriority,
            $newPriority,
            auth()->id() ?? null,
            'priority_change',
            $reason ?? 'Priority updated'
        );
    }

    /**
     * Log ticket assignment change
     */
    public static function logAssignmentChange($ticket, $oldAssignee, $newAssignee, $assignmentType = 'manual', $reason = null)
    {
        return self::logChange(
            $ticket->id,
            'assigned_to',
            $oldAssignee,
            $newAssignee,
            auth()->id() ?? null,
            'assignment',
            $reason ?? "Assignment changed from {$oldAssignee} to {$newAssignee} ({$assignmentType})"
        );
    }

    /**
     * Log SLA change (escalation or reset)
     */
    public static function logSLAChange($ticket, $reason = null)
    {
        return self::logChange(
            $ticket->id,
            'sla_due',
            $ticket->getOriginal('sla_due'),
            $ticket->sla_due,
            auth()->id() ?? null,
            'escalation',
            $reason ?? 'SLA modified'
        );
    }

    /**
     * Log ticket resolution
     */
    public static function logResolution($ticket, $resolutionTime = null, $reason = null)
    {
        return self::logChange(
            $ticket->id,
            'resolved_at',
            null,
            $ticket->resolved_at ?? now(),
            auth()->id() ?? null,
            'resolution',
            $reason ?? "Ticket resolved in {$resolutionTime} hours"
        );
    }
}
