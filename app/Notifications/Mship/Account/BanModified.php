<?php

namespace App\Notifications\Mship\Account;

use App\Models\Mship\Account\Ban;
use Illuminate\Bus\Queueable;
use App\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BanModified extends Notification implements ShouldQueue
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
            ->subject('Account Ban Modified')
            ->view('emails.mship.account.ban.modified', [
                'account' => $this->ban->account,
                'ban' => $this->ban,
                'ban_total_length' => human_diff_string($this->ban->period_start, $this->ban->period_finish)
            ]);
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
