<?php

namespace App\Exceptions\Community;

use App\Models\Community\Group;
use App\Models\Mship\Account;

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
