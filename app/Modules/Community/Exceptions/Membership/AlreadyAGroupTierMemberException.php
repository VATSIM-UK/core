<?php

namespace App\Modules\Community\Exceptions\Membership;

use App\Models\Mship\Account;
use App\Modules\Community\Models\Group;

class AlreadyAGroupTierMemberException extends \Exception
{
    private $account;
    private $group;

    public function __construct(Account $account, Group $group)
    {
        $this->account = $account;
        $this->group = $group;

        $this->message = 'It is not possible to join '.$group->name.' whilst still a member.';
    }

    public function __toString()
    {
        return $this->message;
    }
}
