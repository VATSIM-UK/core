<?php

namespace App\Notifications\Mship;

use App\Models\Mship\Account;
use App\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TwoFactorResetPerformed extends Notification implements ShouldQueue
{
    use Queueable;

    protected Account $target;

    protected Account $administrator;

    protected string $reason;

    public function __construct(Account $target, Account $administrator, string $reason)
    {
        parent::__construct();

        $this->target = $target;
        $this->administrator = $administrator;
        $this->reason = $reason;
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
        $subject = "{$this->administrator->name} reset two-factor authentication for {$this->target->name}";

        return (new MailMessage)
            ->from('admin@vatsim.uk', 'VATSIM UK - Admin')
            ->subject($subject)
            ->view('emails.mship.security.two_factor_reset_performed', [
                'recipient' => $notifiable,
                'subject' => $subject,
                'target' => $this->target,
                'administrator' => $this->administrator,
                'reason' => $this->reason,
            ]);
    }

    /**
     * @param  mixed  $notifiable
     */
    public function toArray($notifiable)
    {
        return [
            'target' => $this->target->id,
            'administrator' => $this->administrator->id,
            'reason' => $this->reason,
        ];
    }
}
