<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Notam_Status extends Enum_Main {
    const DISABLED = 0;
    const PUBLISHED = 80;
    const DELETED = 100;
    
    public static function getDescription($id){
        switch($id){
            case "DISABLED":
                 return "Disabled";
            case "PUBLISHED":
                 return "Published";
            case "DELETED":
                 return "Deleted";
            default:
                 return self::idToType($id);
        }
    }
    
}