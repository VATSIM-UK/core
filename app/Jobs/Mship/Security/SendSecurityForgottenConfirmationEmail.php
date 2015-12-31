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

class SendSecurityForgottenConfirmationEmail extends \App\Jobs\Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $recipient = null;
    private $token = null;

    public function __construct(Account $recipient, \App\Models\Sys\Token $token)
    {
        $this->recipient = $recipient;
        $this->token = $token;
    }

    public function handle(Mailer $mailer)
    {
        $displayFrom = "VATSIM UK - Community Department";
        $subject = "SSO Security - Reset Confirmation";
        $body = \View::make("emails.mship.security.reset_confirmation")
                     ->with("account", $this->recipient)
                     ->with("token", $this->token)
                     ->render();


        $sender = Account::find(VATUK_ACCOUNT_SYSTEM);
        $isHtml = true;
        $systemGenerated = true;
        Bus::dispatch(new CreateNewMessage($sender, $this->recipient, $subject, $body, $displayFrom, $isHtml, $systemGenerated));
    }
}
