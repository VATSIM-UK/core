<?php

namespace App\Notifications\Mship;

use App\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RecoveryCodeUsed extends Notification implements ShouldQueue
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
        $subject = 'A recovery code was used to sign in to your account';

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK Web Services')
            ->subject($subject)
            ->view('emails.mship.security.recovery_code_used', [
                'subject' => $subject,
                'recipient' => $notifiable,
                'account' => $notifiable,
            ]);
    }

    /**
     * @param  mixed  $notifiable
     */
    public function toArray($notifiable)
    {
        return [
            'message' => 'A recovery code was used to sign in to your account.',
        ];
    }
}
