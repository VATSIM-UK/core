<?php

namespace App\Notifications\Training;

use App\Models\Training\WaitingList\WaitingListRetentionCheck;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * This notification is sent to an account when their retention check is sent.
 * This is deliberately not queued, as the processes dispatching this notification
 * are already a queueable job.
 */
class WaitingListRetentionCheckAccountNotification extends Notification
{
    public function __construct(public WaitingListRetentionCheck $retentionCheck)
    {
        // this isn't 100% reliable, but it's the best we can do without
        // subscribing to the NotificationSent event and updating the record there
        $this->retentionCheck->email_sent_at = now();
        $this->retentionCheck->save();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $waitingListAccount = $this->retentionCheck->waitingListAccount;
        $waitingList = $waitingListAccount->waitingList;

        $retentionCheckUrl = route('mship.waiting-lists.retention.token', [
            'token' => $this->retentionCheck->token,
        ]);

        $view = $waitingList->is_vt ? 'emails.training.waiting_list_retention_check_vt' : 'emails.training.waiting_list_retention_check';

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('UK Training Waiting List Retention Check')
            ->view($view, [
                'recipient' => $notifiable,
                'waiting_list_name' => $waitingList->name,
                'retention_check_url' => $retentionCheckUrl,
            ]);
    }
}
