<?php

namespace App\Notifications\Training;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListRetentionChecks;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WaitinglistRetentionCheck extends Notification implements ShouldQueue
{
    use Queueable;

    private $retentionCheck;

    private $verifyToken;

    public function __construct(WaitingListRetentionChecks $retentionCheck, $verifyToken)
    {
        $this->retentionCheck = $retentionCheck;
        $this->verifyToken = $verifyToken;
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
        $waitingListAccount = WaitingList::findWaitingListAccount($this->retentionCheck->waiting_list_account_id);
        $account = Account::find($waitingListAccount->account_id);

        $retentionCheckUrl = route('training.retention.token', [
            'token' => $this->verifyToken,
        ]);

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('UK Training Waiting List Retention Check')
            ->view('emails.training.waint_list_retention_check', [
                'recipient' => $notifiable,
                'waiting_list_name' => $waitingListName,
                'retention_check_url' => $retentionCheckUrl,
                'account' => $account,
            ]);
    }
}
