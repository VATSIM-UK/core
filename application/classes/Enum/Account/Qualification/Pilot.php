<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Account_Qualification_Pilot extends Enum_Account_Qualification {
    const TYPE = "Pilot";
    const P0 = 0;
    const P1 = 1;
    const P2 = 2;
    const P3 = 3;
    const P4 = 4;
    const P5 = 5;
    const P6 = 6;
    const P7 = 7;
    const P8 = 8;
    const P9 = 9;
    
    public static function getDescription($value){
        switch($value){
            case self::P0:
                return "Not Rated";
            case self::P1:
                return "Online Pilot";
            case self::P2:
                return "Pilot Fundamentals";
            case self::P3:
                return "VFR Pilot";
            case self::P4:
                return "IFR Pilot";
            case self::P5:
                return "Advanced IFR Pilot";
            case self::P6:
                return "International and Oceanic Pilot";
            case self::P7:
                return "Helicopter VFR and IFR Pilot";
            case self::P8:
                return "Military Special Operations Pilot";
            case self::P9:
                return "Pilot Flight Instructor";
            default:
                return parent::getDescription($value);
        }
    }
    
    public static function getAbbreviation($value) {
        return self::valueToType($value);
    }
    
    public static function getDisplayString($array, $type='abbreviation'){
        $return = '';
        $k = 1;
        foreach($array as $value){
            if ($k>1){
                $return .= ', ';
            }
            
            if ($type=='description'){
                $return .= self::getDescription($value);
            } else {
                $return .= self::getAbbreviation($value);
            }
            
            $k++;
        }
        
        return $return;
    }
}