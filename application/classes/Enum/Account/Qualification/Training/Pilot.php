<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Account_Qualification_Training_Pilot extends Enum_Account_Qualification {
    const TYPE = "Training_Pilot";
    const I1 = 1;
    const I2 = 2;
    const I3 = 3;
    
    public static function getDescription($value){
        switch($value){
            case self::I1:
            case self::I2:
                return "Instructor";
            case self::I3:
                return "Senior Instructor";
            default:
                return parent::getDescription($value);
        }
    }
}