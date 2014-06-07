<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Account_Status extends Enum_Account {
    const ACTIVE = b"00000";
    const SYSTEM_BANNED = b"0001";
    const NETWORK_SUSPENDED = b"0010";
    const INACTIVE = b"0100";
    const LOCKED = b"1000";
        const SYSTEM = b"1000"; // Alias of LOCKED
    
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