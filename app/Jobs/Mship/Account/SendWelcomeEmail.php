<?php

namespace App\Jobs\Mship\Account;

use App\Jobs\Messages\CreateNewMessage;
use App\Models\Mship\Account;
use Bus;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWelcomeEmail extends \App\Jobs\Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $account = null;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function handle(Mailer $mailer)
    {
        $displayFrom = "VATSIM UK Community Department";
        $subject = "Welcome to VATSIM UK";
        $body = \View::make("emails.mship.account.welcome")->with("account", $this->account)->render();

        $sender = Account::find(VATUK_ACCOUNT_SYSTEM);
        $isHtml = true;
        $systemGenerated = true;
        Bus::dispatch(new CreateNewMessage($sender, $this->account, $subject, $body, $displayFrom, $isHtml, $systemGenerated))->onQueue("low");
    }
}