<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Account_State extends Enum_Main {
    const GUEST = 1;
    const DIVISION = 30;
    const REGION = 40;
    const VISITOR = 50;
    const TRANSFER = 60;
    
    public static function getDescription($id){
        switch($id){
            //case self::SUSPENDED:
            //    return "Suspended";
            //case self::INACTIVE:
            //    return "Inactive Account";
            case self::DIVISION:
                return "Division Member";
            case self::REGION:
                return "Regional Member (NO DIVISION)";
            case self::VISITOR:
                return "Visiting member";
            case self::TRANSFER:
                 return "Transferring member";
            default:
                 return self::idToType($id);
        }
    }
    
}