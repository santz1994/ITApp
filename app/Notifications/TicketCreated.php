<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Ticket;
use App\NotificationSetting;

class TicketCreated extends Notification
{
    use Queueable;

    protected $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket->load(['ticket_status', 'ticket_priority', 'user']);
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
            NotificationSetting::isEnabled('email_ticket_created') &&
            $notifiable->notify_email && 
            $notifiable->notify_ticket_created) {
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
        return (new MailMessage)
                    ->subject('Tiket Baru Dibuat - ' . $this->ticket->ticket_code)
                    ->greeting('Halo ' . $notifiable->name . '!')
                    ->line('Tiket dukungan Anda telah berhasil dibuat.')
                    ->line('**Kode Tiket:** ' . $this->ticket->ticket_code)
                    ->line('**Subjek:** ' . $this->ticket->subject)
                    ->line('**Prioritas:** ' . ($this->ticket->ticket_priority->priority ?? 'N/A'))
                    ->line('**Status:** ' . ($this->ticket->ticket_status->status ?? 'N/A'))
                    ->line('**Deskripsi:**')
                    ->line($this->ticket->description)
                    ->line('Tim dukungan kami akan meninjau tiket Anda dan merespons segera.')
                    ->action('Lihat Tiket', route('tickets.show', $this->ticket->id));
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
