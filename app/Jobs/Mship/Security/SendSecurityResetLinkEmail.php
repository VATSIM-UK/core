<?php

namespace App\Jobs\Mship\Security;

use App\Jobs\Messages\CreateNewMessage;
use App\Models\Mship\Account;
use Bus;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSecurityResetLinkEmail extends \App\Jobs\Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $recipient = null;
    private $password = null;

    public function __construct(Account $recipient, $password)
    {
        $this->recipient = $recipient;
        $this->password = $password;
    }

    public function handle(Mailer $mailer)
    {
        $displayFrom = "VATSIM UK - Community Department";
        $subject = "New Email Added - Verification Required";
        $body = \View::make("emails.mship.security.reset_password")
                     ->with("account", $this->recipient)
                     ->with("password", $this->password)
                     ->render();

        $sender = Account::find(VATUK_ACCOUNT_SYSTEM);
        $isHtml = true;
        $systemGenerated = true;
        Bus::dispatch(new CreateNewMessage($sender, $this->recipient, $subject, $body, $displayFrom,
            $isHtml, $systemGenerated));
    }
}
