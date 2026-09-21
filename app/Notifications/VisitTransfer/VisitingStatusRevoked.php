<?php

namespace App\Notifications\VisitTransfer;

use App\Services\VisitTransfer\VisitingControllerInactivity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to a visiting controller when their visiting rights are automatically
 * revoked for failing the GCAP activity requirements.
 */
class VisitingStatusRevoked extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private string $reason) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->from('community@vatsim.uk', 'VATSIM UK - Community Department')
            ->subject('VATSIM UK Visiting Status Removed')
            ->view('emails.visit-transfer.visiting_status_revoked', [
                'recipient' => $notifiable,
                'reason' => VisitingControllerInactivity::reasonText($this->reason),
            ]);
    }
}
