<?php

namespace App\Jobs\Mship\Account\Ban;

use App\Jobs\Job;
use App\Models\Mship\Account\Ban;
use Illuminate\Contracts\Bus\SelfHandling;

class SendCreationEmail extends Job implements SelfHandling
{
    use InteractsWithQueue, SerializesModels;
    private $account;
    private $ban;

    public function __construct(Ban $ban)
    {
        $this->account = $ban->account;
        $this->ban = $ban;
    }

    public function handle()
    {
        $displayFrom = "VATSIM UK - Community Department";
        $subject = "Account Ban";
        $body = \View::make("emails.mship.account.ban.created")
                     ->with("account", $this->account)
                     ->with("ban", $this->ban)
                     ->render();

        $sender = \App\Models\Mship\Account::find(VATUK_ACCOUNT_SYSTEM);
        $isHtml = true;
        $systemGenerated = true;

        \Bus::dispatch(new \App\Jobs\Messages\CreateNewMessage($sender, $this->recipient, $subject, $body, $displayFrom, $isHtml, $systemGenerated));
    }
}
