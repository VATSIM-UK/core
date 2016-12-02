<?php

namespace App\Jobs\Mship\Security;

use App\Jobs\Job;
use App\Models\Mship\Account;
use App\Models\Sys\Token;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class TriggerPasswordReset extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $token = null;
    private $account = null;

    public function __construct(Token $token)
    {
        $this->token = $token;
        $this->account = $token->related;
    }

    /**
     * Start the process of resetting a user secondary password.
     *
     * This job will generate a new password and immediately dispatch an email to inform them of this.
     *
     * @return void
     */
    public function handle()
    {
        $temporaryPassword = str_random(12);
        $this->account->setPassword($temporaryPassword, true);

        $sendSecurityTemporaryPasswordEmail = new SendSecurityTemporaryPasswordEmail($this->account, $temporaryPassword);
        dispatch($sendSecurityTemporaryPasswordEmail->onQueue('emails'));
    }
}
