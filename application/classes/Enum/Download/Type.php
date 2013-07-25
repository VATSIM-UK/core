<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Download_Type extends Enum_Main {
    const LOCAL = 40;
    const EXTERNAL = 80;
    
    public static function getDescription($id){
        switch($id){
            case "LOCAL":
                 return "Local File/Download";
            case "EXTERNAL":
                 return "Remote File/Download";
            default:
                 return self::idToType($id);
        }
    }
    
}