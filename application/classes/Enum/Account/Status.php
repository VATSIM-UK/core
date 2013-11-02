<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Account_Status extends Enum_Account {
    const ACTIVE = b"0001";
    const SYSTEM_BANNED = b"0010";
    const NETWORK_BANNED = b"0100";
    const INACTIVE = b"1000";
    
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
            default:
                 return parent::getDescription($value);
        }
    }
    
}