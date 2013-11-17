<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_System extends Enum_Main {
    
    public static function getDescription($value){
        switch($value){
            default:
                 return parent::getDescription($value);
        }
    }
    
}
