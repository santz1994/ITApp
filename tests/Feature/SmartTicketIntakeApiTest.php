<?php

namespace Tests\Feature;

use App\KnowledgeBaseArticle;
use App\TicketsPriority;
use App\TicketsType;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartTicketIntakeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_smart_ticket_recommendation(): void
    {
        $user = User::factory()->create();

        $hardwareType = TicketsType::create(['type' => 'Hardware Issue']);
        TicketsType::create(['type' => 'Software Issue']);
        TicketsType::create(['type' => 'Network Problem']);

        $urgentPriority = TicketsPriority::create(['priority' => 'Urgent']);
        TicketsPriority::create(['priority' => 'Normal']);
        TicketsPriority::create(['priority' => 'Low']);

        KnowledgeBaseArticle::create([
            'title' => 'Troubleshooting Office Printer Offline',
            'slug' => 'troubleshooting-office-printer-offline',
            'content' => 'Steps to recover printer when device is offline and cannot print.',
            'category' => 'hardware',
            'tags' => ['printer', 'hardware', 'offline'],
            'author_id' => $user->id,
            'status' => 'published',
            'published_at' => now()->subMinutes(30),
            'views' => 25,
            'helpful_count' => 10,
            'not_helpful_count' => 1,
        ]);

        $payload = [
            'subject' => 'URGENT: Printer in finance room is down',
            'description' => 'Our office printer cannot print and team cannot work during closing process.',
        ];

        $response = $this->actingAs($user)->postJson('/api/v1/tickets/smart-intake', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.recommended.ticket_type_id', $hardwareType->id)
            ->assertJsonPath('data.recommended.ticket_priority_id', $urgentPriority->id)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'recommended' => [
                        'ticket_type_id',
                        'ticket_type_name',
                        'ticket_priority_id',
                        'ticket_priority_name',
                        'type_confidence',
                        'priority_confidence',
                    ],
                    'matched_signals',
                    'knowledge_base_suggestions',
                    'metadata' => ['api_version', 'timezone', 'generated_at', 'token_count'],
                ],
            ]);
    }

    public function test_guest_cannot_access_smart_ticket_intake_endpoint(): void
    {
        $response = $this->postJson('/api/v1/tickets/smart-intake', [
            'subject' => 'Need help',
            'description' => 'Cannot login to application now.',
        ]);

        $response->assertStatus(401);
    }

    public function test_smart_ticket_intake_returns_kb_suggestions_and_jakarta_timezone_metadata(): void
    {
        $user = User::factory()->create();

        TicketsType::create(['type' => 'Access Request']);
        TicketsPriority::create(['priority' => 'Normal']);

        $article = KnowledgeBaseArticle::create([
            'title' => 'Reset Password for Internal System',
            'slug' => 'reset-password-internal-system',
            'content' => 'How to reset account password and unlock access roles safely.',
            'category' => 'access',
            'tags' => ['password', 'access', 'account'],
            'author_id' => $user->id,
            'status' => 'published',
            'published_at' => now()->subHour(),
            'views' => 50,
            'helpful_count' => 20,
            'not_helpful_count' => 2,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/tickets/smart-intake', [
            'subject' => 'Cannot login account after password expired',
            'description' => 'Please help reset password and unlock account access for today.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.metadata.timezone', 'Asia/Jakarta')
            ->assertJsonPath('data.knowledge_base_suggestions.0.id', $article->id);
    }
}
