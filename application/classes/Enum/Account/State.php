<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Account_State extends Enum_Main {
    const NOT_REGISTERED = 0;
    const GUEST = 1;
    //const SUSPENDED = 10;
    //const INACTIVE = 20;
    const DIVISION = 30;
    const REGION = 40;
    const VISITOR = 50;
    const TRANSFER = 60;
    const INTERNATIONAL = 99;
    
    public static function getDescription($value){
        switch($value){
            case self::NOT_REGISTERED:
                return "Not registered";
            case self::GUEST:
                return "Guest Member";
            case self::DIVISION:
                return "Division Member";
            case self::REGION:
                return "Regional Member";
            case self::VISITOR:
                return "Visiting member";
            case self::TRANSFER:
                 return "Transferring member";
            case self::INTERNATIONAL:
                 return "International member";
            default:
                 return parent::getDescription($value);
        }
    }
    
}