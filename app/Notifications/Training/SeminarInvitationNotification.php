<?php

namespace App\Notifications\Training;

use App\Models\Training\Seminar\SeminarInvitation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SeminarInvitationNotification extends Notification
{
    public function __construct(public SeminarInvitation $invitation) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $invitation = $this->invitation->loadMissing('seminar');
        $seminar = $invitation->seminar;
        $copy = $seminar->seminarEmailCopy();

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject($invitation->is_short_notice ? $copy['short_notice_subject'] : $copy['invitation_subject'])
            ->view($copy['invitation_view'], [
                'recipient' => $notifiable,
                'seminar' => $seminar,
                'invitation' => $invitation,
                'acceptUrl' => route('mship.waiting-lists.seminar-invitation.accept', ['token' => $invitation->token]),
                'notInterestedUrl' => route('mship.waiting-lists.seminar-invitation.not-interested', ['token' => $invitation->token]),
                'cannotAttendUrl' => route('mship.waiting-lists.seminar-invitation.cannot-attend', ['token' => $invitation->token]),
            ]);
    }
}
