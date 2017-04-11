<?php

namespace App\Notifications\Mship;

use App\Models\Mship\Account\Ban;
use Illuminate\Bus\Queueable;
use App\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BanRepealed extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Community Department')
            ->subject('Account Ban Repealed')
            ->view('emails.mship.account.ban.repealed', ['account' => $this->ban->account, 'ban' => $this->ban]);
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
