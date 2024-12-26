<?php

namespace App\Notifications\Mship\Endorsement;

use App\Models\Mship\Account\Endorsement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SoloEndorsementNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Endorsement $endorsement)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $endorsementExpiry = $this->endorsement->expires_at->toDayDateTimeString();

        return (new MailMessage)
            ->from(config('mail.from.address', 'VATSIM UK - Training Department'))
            ->subject('Solo Endorsement Granted')
            ->view('emails.mship.endorsement.solo_endorsement_granted', [
                'recipient' => $notifiable,
                'endorsement_expiry' => $endorsementExpiry,
                'endorsement_callsign' => $this->endorsement->endorsable->callsign,
            ]);
    }
}
