<?php

namespace App\Notifications\Mship;

use Illuminate\Bus\Queueable;
use App\Models\Mship\Account\Ban;
use App\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BanCreated extends Notification implements ShouldQueue
{
    use Queueable;

    private $ban;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Ban $ban)
    {
        parent::__construct();

        $this->ban = $ban;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if ($this->ban->is_local) {
            return ['mail', 'database'];
        } else {
            return [];
        }
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $subject = 'New Account Ban';

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Community Department')
            ->subject($subject)
            ->view('emails.mship.account.ban.created', ['subject' => $subject, 'recipient' => $this->ban->account, 'ban' => $this->ban]);
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
            'ban_id' => $this->ban->id,
        ];
    }
}
