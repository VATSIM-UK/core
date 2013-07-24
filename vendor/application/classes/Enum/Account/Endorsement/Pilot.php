<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Account_Endorsement_Pilot extends Enum_Account_Endorsement {
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
}