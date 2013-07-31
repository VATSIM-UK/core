<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_System extends Enum_Main {
    
    public static function getDescription($id){
        switch($id){
            default:
                 return self::idToType($id);
        }
    }
    
}
