<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Account extends Enum_Main {
    const STATUS_FINE = b"000";
    const STATUS_SYSTEM_BANNED = b"001";
    const STATUS_NETWORK_BANNED = b"010";
    const STATUS_INACTIVE = b"100";
    
    public static function getDescription($id){
        switch($id){
            default:
                 return self::valueToType($id);
        }
    }
    
}