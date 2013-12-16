<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Account_Status extends Enum_Account {
    const ACTIVE = b"00001";
    const SYSTEM_BANNED = b"00010";
    const NETWORK_BANNED = b"00100";
    const INACTIVE = b"01000";
    const LOCKED = b"10000";
    
    public static function getDescription($value){
        switch($value){
            case ACTIVE:
                return "Active";
            case SYSTEM_BANNED:
                return "Banned (LOCAL)";
            case NETWORK_BANNED:
                return "Banned (NETWORK)";
            case INACTIVE:
                return "Inactive";
            case LOCKED:
                return "Locked/System";
            default:
                 return parent::getDescription($value);
        }
    }
    
}