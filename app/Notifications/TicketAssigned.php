<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Ticket;
use App\User;
use App\NotificationSetting;

class TicketAssigned extends Notification
{
    use Queueable;

    protected $ticket;
    protected $assignedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, ?User $assignedBy = null)
    {
        $this->ticket = $ticket->load(['ticket_status', 'ticket_priority', 'user']);
        $this->assignedBy = $assignedBy;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        // Check if system-wide email notifications are enabled AND user wants to receive this notification
        if (NotificationSetting::isEnabled('email_enabled') && 
            NotificationSetting::isEnabled('email_ticket_assigned') &&
            $notifiable->notify_email && 
            $notifiable->notify_ticket_assigned) {
            $channels[] = 'mail';
        }

        // Future: WhatsApp and Telegram channels
        // if (NotificationSetting::isEnabled('whatsapp_enabled')) { $channels[] = 'whatsapp'; }
        // if (NotificationSetting::isEnabled('telegram_enabled')) { $channels[] = 'telegram'; }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $assignmentType = $this->ticket->assignment_type === 'auto' ? 'otomatis' : 'manual';
        
        $message = (new MailMessage)
                    ->subject('Tiket Ditugaskan kepada Anda - ' . $this->ticket->ticket_code)
                    ->greeting('Halo ' . $notifiable->name . '!')
                    ->line('Sebuah tiket dukungan telah ditugaskan kepada Anda secara ' . $assignmentType . '.')
                    ->line('**Kode Tiket:** ' . $this->ticket->ticket_code)
                    ->line('**Subjek:** ' . $this->ticket->subject)
                    ->line('**Prioritas:** ' . ($this->ticket->ticket_priority->priority ?? 'N/A'))
                    ->line('**Status:** ' . ($this->ticket->ticket_status->status ?? 'N/A'))
                    ->line('**Dilaporkan oleh:** ' . ($this->ticket->user->name ?? 'N/A'))
                    ->line('**Deskripsi:**')
                    ->line($this->ticket->description);

        if ($this->assignedBy) {
            $message->line('**Ditugaskan oleh:** System');
        }

        $message->action('Lihat Tiket', route('tickets.show', $this->ticket->id))
                ->line('Mohon tinjau dan tanggapi tiket ini sesegera mungkin.');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
