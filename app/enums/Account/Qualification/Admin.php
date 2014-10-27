<?php

namespace Enums\Account\Qualification;

class Admin extends \Enums\Account\Qualification {
    const TYPE = "Admin";
    const SUP = 1;
    const SUP2 = 2;
    const ADM = 3;
    
    public static function getDescription($value){
        switch($value){
            case self::SUP:
                return "Supervisor";
            case self::SUP2:
                return "Senior Supervisor";
            case self::ADM:
                return "Administrator";
            default:
                return parent::getDescription($value);
        }
    }
}