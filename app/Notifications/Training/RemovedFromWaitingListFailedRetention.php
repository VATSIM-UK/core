<?php

namespace App\Notifications\Training;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListRetentionChecks;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RemovedFromWaitingListFailedRetention implements Notification
{
    use Queueable;

    private $retentionCheck;

    public function __construct(WaitingListRetentionChecks $retentionCheck)
    {
        $this->retentionCheck = $retentionCheck;
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
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {

        $waitingListName = $this->retentionCheck->waitingList->name;
        $lastCheckSentAt = $this->retentionCheck->email_sent_at->format('d M Y');
        $removalDate = now()->format('d M Y');
        $waitingListAccount = WaitingList::findWaitingListAccount($this->retentionCheck->waiting_list_account_id);

        $account = Account::find($waitingListAccount->account_id);

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('UK Training Waiting List Removal')
            ->view('emails.training.waiting_list_failed_retention.blade', [
                'recipient' => $notifiable,
                'waiting_list_name' => $waitingListName,
                'last_check_sent_at' => $lastCheckSentAt,
                'removal_date' => $removalDate,
                'account' => $account,
            ]);
    }
}
