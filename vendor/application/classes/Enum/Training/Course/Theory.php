<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Training_Course_Theory extends Enum_Main {
    const NONE = 0;
    const REQUIRED = 1;
    const AVAILABLE = 2;
    
    public static function getDescription($id){
        switch($id){
            case self::NONE:
                 return "No theory assessment";
            case self::REQUIRED:
                 return "Theory assessment is mandatory to course completion";
            case self::AVAILABLE:
                 return "Theory material and/or optional tests are available for this course"; 
            default:
                 return self::idToType($id);
        }
    }
    
}