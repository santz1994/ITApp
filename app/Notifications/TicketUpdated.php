<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Ticket;
use App\NotificationSetting;

class TicketUpdated extends Notification
{
    use Queueable;

    protected $ticket;
    protected $updateType;
    protected $notes;

    /**
     * Create a new notification instance.
     * 
     * @param Ticket $ticket
     * @param string $updateType (e.g., 'status', 'priority', 'general')
     * @param string|null $notes Additional update notes
     */
    public function __construct(Ticket $ticket, string $updateType = 'general', ?string $notes = null)
    {
        $this->ticket = $ticket->load(['ticket_status', 'ticket_priority', 'user']);
        $this->updateType = $updateType;
        $this->notes = $notes;
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
            NotificationSetting::isEnabled('email_ticket_updated') &&
            $notifiable->notify_email && 
            $notifiable->notify_ticket_updated) {
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
        $subject = 'Tiket Diperbarui - ' . $this->ticket->ticket_code;
        
        if ($this->updateType === 'status') {
            $subject = 'Status Tiket Diubah - ' . $this->ticket->ticket_code;
        } elseif ($this->updateType === 'priority') {
            $subject = 'Prioritas Tiket Diubah - ' . $this->ticket->ticket_code;
        }

        $message = (new MailMessage)
                    ->subject($subject)
                    ->greeting('Halo ' . $notifiable->name . '!')
                    ->line('Tiket dukungan Anda telah diperbarui.');

        // Add update type specific information
        if ($this->updateType === 'status') {
            $message->line('**Status Diubah menjadi:** ' . ($this->ticket->ticket_status->status ?? 'N/A'));
        } elseif ($this->updateType === 'priority') {
            $message->line('**Prioritas Diubah menjadi:** ' . ($this->ticket->ticket_priority->priority ?? 'N/A'));
        }

        $message->line('**Kode Tiket:** ' . $this->ticket->ticket_code)
                ->line('**Subjek:** ' . $this->ticket->subject)
                ->line('**Status Saat Ini:** ' . ($this->ticket->ticket_status->status ?? 'N/A'))
                ->line('**Prioritas Saat Ini:** ' . ($this->ticket->ticket_priority->priority ?? 'N/A'));

        if ($this->ticket->assignedTo) {
            $message->line('**Ditugaskan kepada:** ' . $this->ticket->assignedTo->name);
        }

        if ($this->notes) {
            $message->line('**Catatan Pembaruan:**')
                    ->line($this->notes);
        }

        $message->action('Lihat Tiket', route('tickets.show', $this->ticket->id));

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
