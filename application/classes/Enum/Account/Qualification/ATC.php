<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Account_Qualification_ATC extends Enum_Account_Qualification {
    const TYPE = "ATC";
    const OBS = 1;
    const S1 = 2;
    const S2 = 3;
    const S3 = 4;
    const C1 = 5;
    const C2 = 5; // No longer in use.
    const C3 = 7;
    
    public static function getDescription($value, $long=true){
        switch($value){
            case self::OBS:
                return ($long ? "Observer" : "OBS");
            case self::S1:
                return ($long ? "Student 1" : "STU1");
            case self::S2:
                return ($long ? "Student 2" : "STU2");
            case self::S3:
                return ($long ? "Senior Student" : "STU+");
            case self::C1:
            case self::C2:
                return ($long ? "Controller 1" : "CTR");
            case self::C3:
                return ($long ? "Senior Controller" : "CTR+");
            default:
                return parent::getDescription($value);
        }
    }
    
    public static function getPositionSuffixes($value){
        switch($value){
            case self::OBS:
                return "OBS";
            case self::S1:
                return "DEL,GND";
            case self::S2:
                return "DEL,GND,TWR";
            case self::S3:
                return "DEL,GND,TWR,APP";
            case self::C1:
            case self::C2:
            case self::C3:
                return "DEL,GND,TWR,APP,CTR";
            default:
                return "";
        }
    }
}