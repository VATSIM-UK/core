<?php

namespace App\Notifications\Mship\Security;

use App\Models\Sys\Token;
use Illuminate\Bus\Queueable;
use App\Notifications\Notification;
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
    public function __construct(Token $token)
    {
        parent::__construct();

        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $subject = 'SSO Password Reset';

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK Web Services')
            ->subject($subject)
            ->view('emails.mship.security.reset_confirmation', ['subject' => $subject, 'recipient' => $notifiable, 'account' => $notifiable, 'token' => $this->token]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return ['token_id' => $this->token->token_id];
    }
}
