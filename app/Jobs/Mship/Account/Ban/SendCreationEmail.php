<?php

namespace App\Jobs\Mship\Account\Ban;

use App\Jobs\Job;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Ban;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCreationEmail extends Job implements ShouldQueue
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
        if(!$this->ban->is_local){
            return true;
        }

        $displayFrom = "VATSIM UK - Community Department";
        $subject = "Account Ban";
        $body = \View::make("emails.mship.account.ban.created")
                     ->with("recipient", $this->recipient)
                     ->with("ban", $this->ban)
                     ->render();

        $sender = Account::find(VATUK_ACCOUNT_SYSTEM);
        $isHtml = true;
        $systemGenerated = true;

        dispatch(new \App\Jobs\Messages\CreateNewMessage($sender, $this->recipient, $subject, $body, $displayFrom, $isHtml, $systemGenerated));
    }
}
