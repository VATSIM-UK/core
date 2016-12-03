<?php

namespace App\Jobs\Mship\Account;

use App\Jobs\Messages\CreateNewMessage;
use App\Models\Mship\Account;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use View;

class SendSlackInviteEmail extends \App\Jobs\Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $account = null;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function handle()
    {
        if (! $this->account->hasState('DIVISION') && ! $this->account->hasState('VISITING') && ! $this->account->hasState('TRANSFERRING')) {
            return true;  // They can't have Slack access.  Tut.
        }

        $displayFrom = 'VATSIM UK Community Department';
        $subject     = 'Why not join us on Slack?';
        $body        = View::make('emails.mship.account.slack_invite')->with('account', $this->account)->render();

        $sender              = Account::find(VATUK_ACCOUNT_SYSTEM);
        $isHtml              = true;
        $systemGenerated     = true;
        $createNewMessageJob = new CreateNewMessage($sender, $this->account, $subject, $body, $displayFrom, $isHtml, $systemGenerated);

        dispatch($createNewMessageJob->onQueue('emails'));
    }
}
