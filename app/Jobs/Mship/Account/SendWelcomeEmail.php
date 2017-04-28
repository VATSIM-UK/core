<?php

namespace App\Jobs\Mship\Account;

use View;
use App\Models\Mship\Account;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use App\Jobs\Messages\CreateNewMessage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWelcomeEmail extends \App\Jobs\Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $account = null;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function handle(Mailer $mailer)
    {
        $displayFrom = 'VATSIM UK Community Department';
        $subject = 'Welcome to VATSIM UK';
        $body = View::make('emails.mship.account.welcome')->with('account', $this->account)->render();

        $sender = Account::find(VATUK_ACCOUNT_SYSTEM);
        $isHtml = true;
        $systemGenerated = true;
        $createNewMessageJob = new CreateNewMessage($sender, $this->account, $subject, $body, $displayFrom, $isHtml, $systemGenerated);
        dispatch($createNewMessageJob->onQueue('med'));
    }
}
