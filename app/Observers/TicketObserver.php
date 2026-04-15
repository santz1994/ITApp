<?php

namespace App\Observers;

use App\Ticket;
use App\DailyActivity;
use App\Services\CacheService;
use Illuminate\Support\Facades\DB;

class TicketObserver
{
    public function created(Ticket $ticket)
    {
        // Immutable audit
        DB::table('ticket_history')->insert([
            'ticket_id' => $ticket->id,
            'user_id' => $ticket->user_id ?? null,
            'event_type' => 'created',
            'data' => json_encode($ticket->toArray()),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Auto create daily activity when ticket assigned on creation
        if ($ticket->assigned_to) {
            try {
                DailyActivity::create([
                    'user_id' => $ticket->assigned_to,
                    'activity_date' => now(),
                    'description' => 'Tiket baru ditugaskan: ' . $ticket->subject,
                    'ticket_id' => $ticket->id,
                    'type' => 'ticket_assignment',
                    'activity_type' => 'system'
                ]);
            } catch (\Exception $e) {
                // ignore activity creation errors
            }
        }
    }

    public function updated(Ticket $ticket)
    {
        // History creation is now handled by Ticket model's updating() hook
        // which creates TicketHistory records with proper field-level tracking.
        // This observer previously wrote to ticket_history with JSON data,
        // but that conflicted with the new field-level audit trail.

        // If status changed to a completion-like state, create daily activity and set resolved_at
        if ($ticket->wasChanged('ticket_status_id')) {
            try {
                $status = $ticket->ticket_status;
                if ($status && strtolower($status->status ?? $status->name ?? '') === 'resolved') {
                    DailyActivity::create([
                        'user_id' => $ticket->assigned_to ?? $ticket->user_id,
                        'activity_date' => now(),
                        'description' => 'Menyelesaikan Tiket: ' . $ticket->subject .
                                       ($ticket->asset ? ' - Asset: ' . $ticket->asset->asset_tag : ''),
                        'ticket_id' => $ticket->id,
                        'type' => 'ticket_completion',
                        'activity_type' => 'system'
                    ]);

                    if (!$ticket->resolved_at) {
                        $ticket->resolved_at = now();
                        $ticket->saveQuietly();
                    }
                }
            } catch (\Exception $e) {
                // ignore activity errors
            }
        }

        // Reassignment activity
        if ($ticket->wasChanged('assigned_to') && $ticket->assigned_to) {
            try {
                DailyActivity::create([
                    'user_id' => $ticket->assigned_to,
                    'activity_date' => now(),
                    'description' => 'Tiket ditugaskan ulang: ' . $ticket->subject,
                    'ticket_id' => $ticket->id,
                    'type' => 'ticket_reassignment',
                    'activity_type' => 'system'
                ]);
            } catch (\Exception $e) {
                // ignore
            }
        }

        // Clear ticket-related caches
        try {
            app(CacheService::class)->clearCacheOnUpdate('ticket');
        } catch (\Exception $e) {
            // ignore cache clearing errors
        }
    }

    public function deleted(Ticket $ticket)
    {
        DB::table('ticket_history')->insert([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id() ?? null,
            'event_type' => 'deleted',
            'data' => json_encode($ticket->toArray()),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            app(CacheService::class)->clearCacheOnUpdate('ticket');
        } catch (\Exception $e) {
            // ignore
        }
    }
}
