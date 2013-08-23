<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Account_Security extends Enum_Main {
    const UNKNOWN = 0;
    const MEMBER = 10;
    const LOW = 20;
    const MED = 50;
    const HIGH = 100;
    
    public static function getDescription($value){
        switch(self::valueToType($value)){
            case self::MEMBER:
                return "Member Password";
            case self::LOW:
                return "Low Strength Password";
            case self::MED:
                return "Medium Strength Password";
            case self::HIGH:
                return "Maximum Strength Password";
            default:
                return parent::getDescription($value);
        }
    }
}