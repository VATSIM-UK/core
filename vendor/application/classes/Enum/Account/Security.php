<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Account_Security extends Enum_Main {
    const UNKNOWN = 0;
    const MEMBER = 10;
    const LOW = 20;
    const MED = 50;
    const HIGH = 100;
    
    public static function getDescription($id){
        switch(self::valueToType($id)){
            case "MEMBER":
                return "Member Password";
            case "LOW":
                return "Low Strength Password";
            case "MED":
                return "Medium Strength Password";
            case "HIG":
                return "Maximum Strength Password";
            default:
                return "unknown";
        }
    }
}