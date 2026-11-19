<?php

namespace App\Notifications\Mship;

use App\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TwoFactorChallengeLocked extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param  mixed  $notifiable
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable)
    {
        $subject = 'Two-factor authentication locked after repeated failed attempts';

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK Web Services')
            ->subject($subject)
            ->view('emails.mship.security.two_factor_challenge_locked', [
                'subject' => $subject,
                'recipient' => $notifiable,
            ]);
    }

    /**
     * @param  mixed  $notifiable
     */
    public function toArray($notifiable)
    {
        return [
            'message' => 'Two-factor authentication was locked after repeated failed attempts.',
        ];
    }
}
