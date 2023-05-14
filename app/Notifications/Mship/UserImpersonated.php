<?php

namespace App\Notifications\Mship;

use App\Models\Mship\Account;
use App\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UserImpersonated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var Account
     */
    protected $target;

    /**
     * @var Account
     */
    protected $impersonator;

    /**
     * @var string
     */
    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Account $target, Account $impersonator, $reason)
    {
        parent::__construct();

        $this->target = $target;
        $this->impersonator = $impersonator;
        $this->reason = $reason;
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
        $subject = "{$this->impersonator->name} impersonated {$this->target->name}";

        return (new MailMessage)
            ->from('admin@vatsim.uk', 'VATSIM UK - Admin')
            ->subject($subject)
            ->view('emails.mship.user_impersonated', [
                'recipient' => $notifiable,
                'subject' => $subject,
                'target' => $this->target,
                'impersonator' => $this->impersonator,
                'reason' => $this->reason,
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
            'target' => $this->target->id,
            'impersonator' => $this->impersonator->id,
            'reason' => $this->reason,
        ];
    }
}
