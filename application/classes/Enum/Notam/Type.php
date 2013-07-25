<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Notam_Type extends Enum_Main {
    const INFO = 20;
    const ANNOUNCEMENT = 50;
    const IMPORTANT = 80;
    
    public static function getDescription($id){
        switch($id){
            case "INFO":
                 return "General Information";
            case "ANNOUNCEMENT":
                 return "Announcement";
            case "IMPORTANT":
                 return "Important Announcement";
            default:
                 return self::idToType($id);
        }
    }
    
}