<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_System_Postmaster_Queue_Status extends Enum_System {
    const QUEUED = 10;
    const PARSED = 40;
    const DELAYED = 90;
    const DISPATCHED = 100;
    const SENT = 100;
    
    public static function getDescription($value){
        switch($value){
            case QUEUED:
                return "Queued";
            case PARSED:
                return "Parsed";
            case DELAYED:
                return "Delayed (Attempted to send, but couldn't)";
            case DISPATCHED:
            case SENT:
                return "Dispatched from Server";
            default:
                return parent::getDescription($value);
        }
    }
}