<?php

namespace App\Jobs\Mship\Security;

use App\Jobs\Job;
use App\Models\Sys\Token;
use App\Models\Mship\Account;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class TriggerPasswordResetConfirmation extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $account = null;
    private $admin_reset = false;

    public function __construct(Account $account, $admin_reset = false)
    {
        $this->account = $account;
        $this->admin_reset = $admin_reset;
    }

    /**
     * Start the process of confirming a user wishes to reset their password.
     *
     * This job will generate a new token and then dispatch the appropriate email job (immediately).
     *
     * @return void
     */
    public function handle()
    {
        $tokenType = 'mship_account_security_reset';
        $allowDuplicates = false;
        $generatedToken = Token::generate($tokenType, $allowDuplicates, $this->account);

        if ($this->admin_reset) {
            $sendConfirmationEmailJob = new SendSecurityForgottenAdminConfirmationEmail($this->account, $generatedToken);
        } else {
            $sendConfirmationEmailJob = new SendSecurityForgottenConfirmationEmail($this->account, $generatedToken);
        }

        dispatch($sendConfirmationEmailJob);
    }
}
