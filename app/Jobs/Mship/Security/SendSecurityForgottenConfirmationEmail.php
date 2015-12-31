<?php

namespace App\Jobs\Mship\Security;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSecurityForgottenConfirmationEmail extends \App\Jobs\Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $recipient = null;
    private $token = null;

    public function __construct(\App\Models\Mship\Account $recipient, \App\Models\Sys\Token $token)
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
        \Bus::dispatch(new \App\Jobs\Messages\CreateNewMessage(\App\Models\Mship\Account::find(VATUK_ACCOUNT_SYSTEM), $this->recipient, $subject, $body, $displayFrom, true, true));
    }
}
