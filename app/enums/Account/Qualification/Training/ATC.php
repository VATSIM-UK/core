<?php

namespace Enums\Account\Qualification\Training;

class ATC extends \Enums\Account\Qualification {
    const TYPE = "Training_ATC";
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