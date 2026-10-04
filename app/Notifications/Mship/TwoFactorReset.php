<?php

namespace App\Notifications\Mship;

use App\Models\Mship\Account;
use App\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TwoFactorReset extends Notification implements ShouldQueue
{
    use Queueable;

    protected Account $administrator;

    public function __construct(Account $administrator)
    {
        parent::__construct();

        $this->administrator = $administrator;
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
        $subject = 'Your two-factor authentication has been reset';

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK Web Services')
            ->subject($subject)
            ->view('emails.mship.security.two_factor_reset', [
                'subject' => $subject,
                'recipient' => $notifiable,
                'account' => $notifiable,
                'administrator' => $this->administrator,
            ]);
    }

    /**
     * @param  mixed  $notifiable
     */
    public function toArray($notifiable)
    {
        return ['administrator' => $this->administrator->id];
    }
}
