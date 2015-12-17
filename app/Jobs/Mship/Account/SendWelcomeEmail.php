<?php

namespace App\Jobs\Mship\Account;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWelcomeEmail extends \App\Jobs\Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $account = null;

    public function __construct(\App\Models\Mship\Account $account)
    {
        $this->account = $account;
    }

    public function handle(Mailer $mailer)
    {
        $displayFrom = "VATSIM UK Community Department";
        $subject = "Welcome to VATSIM UK";
        $body = \View::make("emails.mship.account.welcome")->with("account", $this->account)->render();
        \Bus::dispatch(new \App\Jobs\Messages\CreateNewMessage(\App\Models\Mship\Account::find(VATUK_ACCOUNT_SYSTEM), $this->account, $subject, $body, $displayFrom, true, true));
    }
}