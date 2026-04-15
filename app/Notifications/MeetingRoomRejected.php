<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\MeetingRoomBooking;
use App\NotificationSetting;

class MeetingRoomRejected extends Notification
{
    use Queueable;

    protected $booking;
    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(MeetingRoomBooking $booking, $reason = null)
    {
        $this->booking = $booking;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = [];
        
        // Check if system-wide email notifications are enabled AND user wants to receive this notification
        if (NotificationSetting::isEnabled('email_enabled') && 
            NotificationSetting::isEnabled('email_meeting_rejection') &&
            $notifiable->notify_email && 
            $notifiable->notify_meeting_rejected) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking;
        
        $message = (new MailMessage)
            ->subject('Pemesanan Ruang Meeting Ditolak - ' . $booking->room_name)
            ->error()
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Mohon maaf, pemesanan ruang meeting Anda telah **ditolak**.')
            ->line('')
            ->line('**Detail Pemesanan:**')
            ->line('Ruangan: **' . $booking->room_name . '**')
            ->line('Tanggal: **' . $booking->start_datetime->format('d F Y') . '**')
            ->line('Waktu: **' . $booking->start_datetime->format('H:i') . ' - ' . $booking->end_datetime->format('H:i') . '**')
            ->line('Keperluan: ' . $booking->purpose)
            ->line('');
        
        if ($this->reason) {
            $message->line('**Alasan Penolakan:**')
                    ->line($this->reason)
                    ->line('');
        }
        
        if ($booking->approver) {
            $message->line('Ditolak oleh: System')
                    ->line('Tanggal: ' . $booking->approved_at->format('d M Y, H:i'));
        }
        
        $message->line('')
                ->line('Anda dapat mengajukan pemesanan baru jika diperlukan.')
                ->action('Buat Pemesanan Baru', url('/meeting-room-bookings/create'));
        
        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'room_name' => $this->booking->room_name,
            'status' => 'rejected',
            'reason' => $this->reason,
        ];
    }
}
