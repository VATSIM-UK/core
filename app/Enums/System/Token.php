<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_System_Token extends Enum_System {
    const SECURITY_RESET = 10;
    const EMAIL_CONFIRM = 50;
    
    public static function getDescription($value){
        switch($value){
            case SECURITY_RESET:
                return "Security Reset Confirmation";
            case EMAIL_CONFIRM:
                return "Email Confirmation";
            default:
                return parent::getDescription($value);
        }
    }
}