<?php

namespace Tests\Unit;

use App\Repositories\Dashboard\DashboardRepository;
use App\Ticket;
use App\TicketsStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_ticket_stats_counts_open_and_overdue_tickets(): void
    {
        $openStatus = TicketsStatus::factory()->create(['status' => 'Open']);
        $inProgressStatus = TicketsStatus::factory()->create(['status' => 'In Progress']);
        $resolvedStatus = TicketsStatus::factory()->create(['status' => 'Resolved']);

        Ticket::factory()->create([
            'ticket_status_id' => $openStatus->id,
            'sla_due' => now('Asia/Jakarta')->subHour(),
        ]);

        Ticket::factory()->create([
            'ticket_status_id' => $inProgressStatus->id,
            'sla_due' => now('Asia/Jakarta')->addHours(2),
        ]);

        Ticket::factory()->create([
            'ticket_status_id' => $resolvedStatus->id,
            'sla_due' => now('Asia/Jakarta')->addHours(5),
        ]);

        $repository = app(DashboardRepository::class);
        $stats = $repository->getTicketStats();

        $this->assertSame(2, $stats['open_tickets']);
        $this->assertSame(1, $stats['overdue_tickets']);
    }

    public function test_get_recent_tickets_returns_latest_rows_with_relations_loaded(): void
    {
        TicketsStatus::factory()->create(['status' => 'Open']);

        $oldest = Ticket::factory()->create([
            'created_at' => now('Asia/Jakarta')->subMinutes(30),
            'updated_at' => now('Asia/Jakarta')->subMinutes(30),
        ]);

        $middle = Ticket::factory()->create([
            'created_at' => now('Asia/Jakarta')->subMinutes(20),
            'updated_at' => now('Asia/Jakarta')->subMinutes(20),
        ]);

        $newest = Ticket::factory()->create([
            'created_at' => now('Asia/Jakarta')->subMinutes(10),
            'updated_at' => now('Asia/Jakarta')->subMinutes(10),
        ]);

        $repository = app(DashboardRepository::class);
        $recentTickets = $repository->getRecentTickets(2);

        $this->assertCount(2, $recentTickets);
        $this->assertSame([$newest->id, $middle->id], $recentTickets->pluck('id')->all());
        $this->assertNotContains($oldest->id, $recentTickets->pluck('id')->all());

        $this->assertTrue($recentTickets->first()->relationLoaded('user'));
        $this->assertTrue($recentTickets->first()->relationLoaded('ticket_status'));
        $this->assertTrue($recentTickets->first()->relationLoaded('ticket_priority'));
        $this->assertTrue($recentTickets->first()->relationLoaded('location'));
    }
}