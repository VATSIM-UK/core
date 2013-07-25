<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Account_Qualification_ATC extends Enum_Account_Qualification {
    const OBS = 1;
    const S1 = 2;
    const S2 = 3;
    const S3 = 4;
    const C1 = 5;
    const C2 = 5; // No longer in use.
    const C3 = 7;
    
    public static function getDescription($id){
        switch($id){
            case self::OBS:
                return "Observer";
            case self::S1:
                return "Student";
            case self::S2:
                return "Student 2";
            case self::S3:
                return "Senior Student";
            case self::C1:
            case self::C2:
                return "Controller";
            case self::C3:
                return "Senior Controller";
            default:
                return parent::getDescription($id);
        }
    }
    
    public static function getPositionSuffixes($id){
        switch($id){
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