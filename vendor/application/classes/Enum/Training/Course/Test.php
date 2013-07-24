<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Training_Course_Test extends Enum_Main {
    const MODULE = 0;
    const END = 1;
    const BEFORE = 2;
    const OPTIONAL = 3;
    
    public static function getDescription($id){
        switch($id){
            case self::MODULE:
                 return "Required module test";
            case self::END:
                 return "End of course assessment";
            case self::BEFORE:
                 return "Pre-course assessment";
            case self::OPTIONAL:
                 return "Optional theory test";
            default:
                 return false;
        }
    }
    
}