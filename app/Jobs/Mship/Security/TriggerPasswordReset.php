<?php

namespace App\Jobs\Mship\Security;

use App\Jobs\Job;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Security;
use App\Models\Sys\Token;
use Bus;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Bus\SelfHandling;

class TriggerPasswordReset extends Job implements SelfHandling, ShouldQueue {
    use InteractsWithQueue, SerializesModels;

    private $token = null;
    private $account = null;

    public function __construct(Token $token){
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
    public function handle(){
        $shouldBeHashed = false;
        $temporaryPassword = Security::generate($shouldBeHashed);

        $passwordType = $this->account->current_security ? $this->account->current_security : \App\Models\Mship\Security::getDefault();

        $isTemporary = true;
        $this->account->setPassword($temporaryPassword, $passwordType, $isTemporary);

        Bus::dispatchNow(new SendSecurityTemporaryPasswordEmail($this->account, $temporaryPassword));
    }
}
