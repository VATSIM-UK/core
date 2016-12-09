<?php

namespace App\Modules\Community\Exceptions\Membership;

use App\Models\Mship\Account;

class MustBeADivisionMemberException extends \Exception
{
    private $account;

    public function __construct(Account $account)
    {
        $this->account = $account;

        $this->message = 'It is not possible to join a UK Community Group unless you are a UK member.';
    }

    public function __toString()
    {
        return $this->message;
    }
}
