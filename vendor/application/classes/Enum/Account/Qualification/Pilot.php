<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Account_Qualification_Pilot extends Enum_Account_Qualification {
    const P0 = 0;
    const P1 = 1;
    const P2 = 2;
    const P3 = 3;
    
    public static function getDescription($id){
        switch($id){
            case self::P0:
                return "Not Rated";
            case self::P1:
                return "Online Pilot";
            case self::P2:
                return "Pilot Fundamentals";
            case self::P3:
                return "VFR Pilot";
            default:
                return parent::getDescription($id);
        }
    }
    
    public static function getAbbreviation($id) {
        switch($id){
            case self::P0:
                return "P0";
            case self::P1:
                return "P1";
            case self::P2:
                return "P2";
            case self::P3:
                return "P3";
            default:
                return "Unknown";
        }
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