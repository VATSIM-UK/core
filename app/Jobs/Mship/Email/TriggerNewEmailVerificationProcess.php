<?php

namespace App\Jobs\Mship\Email;

use App\Jobs\Job;
use App\Jobs\Mship\Account\SendNewEmailVerificationEmail;
use App\Models\Mship\Account;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TriggerNewEmailVerificationProcess extends Job implements SelfHandling, ShouldQueue {
    use InteractsWithQueue, SerializesModels;

    private $email = null;
    private $account = null;

    public function __construct(Account\Email $email){
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
    public function handle(){
        $tokenType = "mship_account_email_verify";
        $allowDuplicates = false;
        $generatedToken = Token::generate($tokenType, $allowDuplicates, $this->email);

        Bus::dispatchNow(new SendNewEmailVerificationEmail($this->account, $generatedToken))->onQueue("med");;
    }
}
