<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_System_Postmaster_Priority extends Enum_System {
    const LOW = 10;
    const STANDARD = 50;
    const HIGH = 75;
    const URGENT = 100;
    
    public static function getDescription($value){
        switch($value){
            case LOW:
                return "Low Priority";
            case STANDARD:
                return "Standard";
            case HIGH:
                return "High";
            case URGENT:
                return "Urgent";
            default:
                return parent::getDescription($value);
        }
    }
}