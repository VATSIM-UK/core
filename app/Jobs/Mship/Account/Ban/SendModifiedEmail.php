<?php

namespace App\Jobs\Mship\Account\Ban;

use App\Jobs\Job;
use App\Models\Mship\Account\Ban;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendModifiedEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    private $recipient;
    private $ban;

    public function __construct(Ban $ban)
    {
        $this->recipient = $ban->account;
        $this->ban = $ban;
    }

    public function handle()
    {
        if (! $this->ban->is_local) {
            return true;
        }

        $ban_total_length = human_diff_string($this->ban->period_start, $this->ban->period_finish);

        $displayFrom = 'VATSIM UK - Community Department';
        $subject = 'Account Ban - Updated';
        $body = \View::make('emails.mship.account.ban.modified')
                     ->with('account', $this->recipient)
                     ->with('ban', $this->ban)
                     //->with("ban_difference_type", $ban_difference_type)
                     //->with("ban_difference_amount", $ban_difference_amount)
                     ->with('ban_total_length', $ban_total_length)
                     ->render();

        $sender = \App\Models\Mship\Account::find(VATUK_ACCOUNT_SYSTEM);
        $isHtml = true;
        $systemGenerated = true;

        dispatch(new \App\Jobs\Messages\CreateNewMessage($sender, $this->recipient, $subject, $body, $displayFrom, $isHtml, $systemGenerated));
    }
}
