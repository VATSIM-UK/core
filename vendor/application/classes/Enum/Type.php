<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Type {
    const ATC = 1;
    const PILOT = 2;
    const BOTH = 3;
    
    public static function getType($id, $default='ATC'){
        switch($id){
             case self::ATC:
                  return 'ATC';
             case self::PILOT:
                  return 'Pilot';
             case self::BOTH:
                  return $default;
             default:
                  return false;
        }
    }
    
}