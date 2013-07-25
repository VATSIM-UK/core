<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Content_Type extends Enum_Main {
    const PAGE = 20;
    const CATEGORY = 60;
    
    public static function getDescription($id){
        switch($id){
            case "PAGE":
                 return "Page";
            case "CATEGORY":
                 return "Category";
            default:
                 return self::idToType($id);
        }
    }
    
}