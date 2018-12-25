<?php

namespace App\Notifications\Mship;

use App\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ForgottenPasswordLink extends Notification implements ShouldQueue
{
    use Queueable;

    private $token;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        parent::__construct();

        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $subject = 'SSO Password Reset';

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK Web Services')
            ->subject($subject)
            ->view('emails.mship.security.reset_confirmation', ['subject' => $subject, 'recipient' => $notifiable, 'account' => $notifiable, 'token' => route('password.reset', $this->token)]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return ['token' => $this->token];
    }
}
