<?php

namespace App\Notifications\Mship;

use App\Models\Sys\Token;
use App\Models\Mship\Account;
use Illuminate\Bus\Queueable;
use App\Models\Mship\Account\Email;
use App\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailVerification extends Notification implements ShouldQueue
{
    use Queueable;

    private $token;
    private $email;

    /**
     * Create a new notification instance.
     *
     * @param Email $email
     * @param Token $token
     */
    public function __construct(Email $email, Token $token)
    {
        parent::__construct();

        $this->email = $email;
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
     * @param  mixed $notifiable
     * @return \Illuminate\Mail\Mailable
     */
    public function toMail($notifiable)
    {
        $mailable = new class($notifiable, $this->token) extends \Illuminate\Mail\Mailable {
            use Queueable, SerializesModels;

            private $account;
            private $token;

            /**
             * Create a new message instance.
             *
             * @param Account $account
             * @param Token $token
             */
            public function __construct(Account $account, Token $token)
            {
                $this->account = $account;
                $this->token = $token;
            }

            /**
             * Build the message.
             *
             * @return $this
             */
            public function build()
            {
                $subject = 'New Email Verification';

                return $this->from(config('mail.from.address'), 'VATSIM UK Web Services')
                    ->subject($subject)
                    ->view('emails.mship.account.email_add', [
                        'account' => $this->account,
                        'token' => $this->token,
                        'subject' => $subject,
                        'recipient' => $this->account,
                    ]);
            }
        };

        return $mailable->to($this->email->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'email' => $this->email->email,
            'token_id' => $this->token->token_id,
        ];
    }
}
