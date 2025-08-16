<?php

namespace App\Notifications\Training;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList\WaitingListRetentionCheck;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * This notification is sent to an account when their retention check fails.
 * This is deliberately not queued, as the processes dispatching this notification
 * are already a queueable job.
 */
class RemovedFromWaitingListFailedRetention extends Notification
{
    public function __construct(private WaitingListRetentionCheck $retentionCheck) {}

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
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $waitingListName = $this->retentionCheck->waitingListAccount->waitingList->name;
        $lastCheckSentAt = $this->retentionCheck->email_sent_at->format('d M Y');
        $removalDate = now()->format('d M Y');

        $account = $this->retentionCheck->waitingListAccount->account;

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('UK Training Waiting List Removal')
            ->view('emails.training.waiting_list_failed_retention', [
                'recipient' => $notifiable,
                'waiting_list_name' => $waitingListName,
                'last_check_sent_at' => $lastCheckSentAt,
                'removal_date' => $removalDate,
                'account' => $account,
            ]);
    }
}
