<?php

namespace App\Jobs\Mship\Security;

use View;
use App\Jobs\Job;
use App\Models\Mship\Account;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use App\Jobs\Messages\CreateNewMessage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSecurityTemporaryPasswordEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $recipient = null;
    private $password  = null;

    public function __construct(Account $recipient, $password)
    {
        $this->recipient = $recipient;
        $this->password  = $password;
    }

    /**
     * Dispatch the newly generated password for a user.
     *
     * @param \Illuminate\Contracts\Mail\Mailer $mailer
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        $displayFrom = 'VATSIM UK - Community Department';
        $subject     = 'SSO Security - New Password';
        $body        = View::make('emails.mship.security.reset_password')
                     ->with('account', $this->recipient)
                     ->with('password', $this->password)
                     ->render();

        $sender           = Account::find(VATUK_ACCOUNT_SYSTEM);
        $isHtml           = true;
        $systemGenerated  = true;
        $createNewMessage = new CreateNewMessage($sender, $this->recipient, $subject, $body, $displayFrom, $isHtml, $systemGenerated);
        dispatch($createNewMessage->onQueue('emails'));
    }
}
