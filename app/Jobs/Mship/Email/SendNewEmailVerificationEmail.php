<?php

namespace App\Jobs\Mship\Email;

use App\Jobs\Job;
use App\Jobs\Messages\CreateNewMessage;
use App\Models\Mship\Account;
use App\Models\Sys\Token;
use Bus;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewEmailVerificationEmail extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $recipient = null;
    private $token = null;

    public function __construct(Account\Email $email)
    {
        $this->email = $email;
        $this->account = $email->account;
    }

    public function handle(Mailer $mailer)
    {
        $displayFrom = "VATSIM UK - Community Department";
        $subject = "New Email Added - Verification Required";
        $body = \View::make("emails.mship.account.email_add")
                     ->with("account", $this->recipient)
                     ->with("token", $this->token)
                     ->render();

        $sender = Account::find(VATUK_ACCOUNT_SYSTEM);
        $isHtml = true;
        $systemGenerated = true;
        Bus::dispatch(new CreateNewMessage($sender, $this->recipient, $subject, $body, $displayFrom, $isHtml, $systemGenerated))->onQueue("med");
    }
}