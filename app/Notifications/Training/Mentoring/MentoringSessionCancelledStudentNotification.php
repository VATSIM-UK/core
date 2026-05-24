<?php

namespace App\Notifications\Training\Mentoring;

use App\Models\Cts\Session;
use App\Models\Mship\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MentoringSessionCancelledStudentNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Session $session,
        private Account $cancelledByMentor,
        private string $reason,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->session->loadMissing('student');

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('Your Mentoring Session has been Cancelled')
            ->view('emails.training.mentoring.session_cancelled_student', [
                'recipient' => $notifiable,
                'session' => $this->session,
                'cancelledByMentor' => $this->cancelledByMentor,
                'reason' => $this->reason,
            ]);
    }
}
