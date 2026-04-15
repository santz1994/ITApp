<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\MeetingRoomBooking;
use App\NotificationSetting;

class MeetingRoomApproved extends Notification
{
    use Queueable;

    protected $booking;
    protected $notes;

    /**
     * Create a new notification instance.
     */
    public function __construct(MeetingRoomBooking $booking, $notes = null)
    {
        $this->booking = $booking;
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
            NotificationSetting::isEnabled('email_meeting_approval') &&
            $notifiable->notify_email && 
            $notifiable->notify_meeting_approved) {
            $channels[] = 'mail';
        }
        
        // Future: Add WhatsApp and Telegram channels
        // if (NotificationSetting::isEnabled('whatsapp_enabled')) {
        //     $channels[] = 'whatsapp';
        // }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking;
        
        $message = (new MailMessage)
            ->subject('Pemesanan Ruang Meeting Disetujui - ' . $booking->room_name)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Pemesanan ruang meeting Anda telah **disetujui**!')
            ->line('')
            ->line('**Detail Pemesanan:**')
            ->line('Ruangan: **' . $booking->room_name . '**')
            ->line('Tanggal: **' . $booking->start_datetime->format('d F Y') . '**')
            ->line('Waktu: **' . $booking->start_datetime->format('H:i') . ' - ' . $booking->end_datetime->format('H:i') . '**')
            ->line('Keperluan: ' . $booking->purpose)
            ->line('Jumlah Peserta: ' . $booking->attendees_count . ' orang')
            ->line('');
        
        if ($this->notes) {
            $message->line('**Catatan Persetujuan:**')
                    ->line($this->notes)
                    ->line('');
        }
        
        if ($booking->approver) {
            $message->line('Disetujui oleh: System')
                    ->line('Tanggal: ' . $booking->approved_at->format('d M Y, H:i'));
        }
        
        $message->action('Lihat Detail Pemesanan', url('/meeting-room-bookings/' . $booking->id))
                ->line('Terima kasih telah menggunakan Sistem Pemesanan Ruang Meeting kami!')
                ->line('');
        
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
            'booking_id' => $this->booking->id,
            'room_name' => $this->booking->room_name,
            'status' => 'approved',
            'notes' => $this->notes,
        ];
    }
}
