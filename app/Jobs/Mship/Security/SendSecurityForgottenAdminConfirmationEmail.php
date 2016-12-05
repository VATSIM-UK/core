<?php

namespace App\Jobs\Mship\Security;

use View;
use App\Models\Sys\Token;
use App\Models\Mship\Account;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use App\Jobs\Messages\CreateNewMessage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSecurityForgottenAdminConfirmationEmail extends \App\Jobs\Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $recipient = null;
    private $token     = null;

    public function __construct(Account $recipient, Token $token)
    {
        $this->recipient = $recipient;
        $this->token     = $token;
    }

    /**
     * Dispatch the security password reset CONFIRMATION email (for admin resets).
     *
     * @param \Illuminate\Contracts\Mail\Mailer $mailer
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        $displayFrom = 'VATSIM UK - Community Department';
        $subject     = 'SSO Security - Administrative Reset Confirmation';
        $body        = View::make('emails.mship.security.reset_confirmation_admin')
                     ->with('account', $this->recipient)
                     ->with('token', $this->token)
                     ->render();

        $sender           = Account::find(VATUK_ACCOUNT_SYSTEM);
        $isHtml           = true;
        $systemGenerated  = true;
        $createNewMessage = new CreateNewMessage($sender, $this->recipient, $subject, $body, $displayFrom, $isHtml, $systemGenerated);
        dispatch($createNewMessage->onQueue('emails'));
    }
}
