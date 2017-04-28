<?php

namespace App\Jobs\Mship\Email;

use App\Jobs\Job;
use App\Models\Sys\Token;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Email;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class TriggerNewEmailVerificationProcess extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $email = null;
    private $account = null;

    public function __construct(Account\Email $email)
    {
        $this->email = $email;
        $this->account = $email->account;
    }

    /**
     * Start the process for verifying a newly added email to an account.
     *
     * This will:
     * * Generate a token
     * * Dispatch an email IMMEDIATELY (med queue).
     *
     * @return void
     */
    public function handle()
    {
        $tokenType = 'mship_account_email_verify';
        $allowDuplicates = false;
        $generatedToken = Token::generate($tokenType, $allowDuplicates, $this->email);

        $sendNewEmailVerificationEmail = new SendNewEmailVerificationEmail($this->email, $generatedToken);
        dispatch($sendNewEmailVerificationEmail->onQueue('med'));
    }
}
