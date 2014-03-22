<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_System_Access extends Enum_System {
    const RTS = 10;
    const HELPDESK = 20;
    const WEBSITE = 30;
    const FORUM = 50;
    const TEAMSPEAK = 70;
    
    public static function getDescription($value){
        switch($value){
            case RTS:
                return "RTS System";
            case HELPDESK:
                return "Helpdesk";
            case WEBSITE:
                return "Website";
            case FORUM:
                return "Forum/Community Board";
            case TEAMSPEAK:
                return "Teamspeak Registration System";
            default:
                return parent::getDescription($value);
        }
    }
}