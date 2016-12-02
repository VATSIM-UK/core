<?php

namespace App\Exceptions\Mship;

class DuplicateRoleException extends \Exception
{
    private $role;

    public function __construct($role)
    {
        $this->role = $role;
    }

    public function __toString()
    {
        return "The role '".$this->role->name."' is already attached to this account.";
    }
}
