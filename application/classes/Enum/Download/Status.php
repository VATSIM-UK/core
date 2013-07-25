<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Download_Status extends Enum_Main {
    const DISABLED = 0;
    const PENDING_APPROVAL = 30;
    const PUBLISHED = 80;
    
    public static function getDescription($id){
        switch($id){
            case "DISABLED":
                 return "Disabled";
            case "PENDING_APPROVAL":
                 return "Pending Approval";
            case "PUBLISHED":
                 return "Published";
            default:
                 return self::idToType($id);
        }
    }
    
}