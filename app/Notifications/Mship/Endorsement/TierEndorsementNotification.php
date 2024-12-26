<?php

namespace App\Notifications\Mship\Endorsement;

use App\Models\Mship\Account\Endorsement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TierEndorsementNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Endorsement $endorsement) {}

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
        $positions = $this->endorsement->load('endorsable')->endorsable->positions->map(fn ($position) => "$position->name ($position->description)");

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('Tier Endorsement Granted')
            ->view('emails.mship.endorsement.tier_endorsement_granted', ['recipient' => $notifiable, 'positions' => $positions, 'endorsement_name' => $this->endorsement->endorsable->name]);
    }
}
