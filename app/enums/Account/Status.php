<?php

namespace Enums\Account;

class Status extends \Enums\Base {
    const ACTIVE = 0; //b"00000";
    const SYSTEM_BANNED = 1; //b"0001";
    const NETWORK_SUSPENDED = 2; //b"0010";
    const INACTIVE = 4; //b"0100";
    const LOCKED = 8; //b"1000";
        const SYSTEM = 8; //b"1000"; // Alias of LOCKED

    public static function getDescription($value){
        switch($value){
            case self::ACTIVE:
                return "Active";
            case self::SYSTEM_BANNED:
                return "Banned (LOCAL)";
            case self::NETWORK_SUSPENDED:
                return "Suspended (NETWORK)";
            case self::INACTIVE:
                return "Inactive";
            case self::LOCKED:
            case self::SYSTEM:
                return "Locked/System";
            default:
                 return parent::getDescription($value);
        }
    }

}