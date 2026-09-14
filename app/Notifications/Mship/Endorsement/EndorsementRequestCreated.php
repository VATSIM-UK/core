<?php

namespace App\Notifications\Mship\Endorsement;

use App\Enums\EmailType;
use App\Models\Mship\Account\EndorsementRequest;
use App\Notifications\Contracts\HasEmailType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EndorsementRequestCreated extends Notification implements HasEmailType, ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private EndorsementRequest $endorsementRequest) {}

    public function getEmailType(): EmailType
    {
        return EmailType::EndorsementRequestCreated;
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
        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('Endorsement Request Created')
            ->view('emails.mship.endorsement.endorsement_request_created', [
                'recipient' => $notifiable,
                'requestIndexUrl' => url('/admin/endorsement-requests'),
                'endorsementRequest' => $this->endorsementRequest,
                'requester' => $this->endorsementRequest->requester,
                'account' => $this->endorsementRequest->account,
            ]);
    }
}
