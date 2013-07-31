<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Account_Endorsement extends Enum_Main {
    const UNKNOWN = 0;
    
    public static function getDescription($value){
        return parent::getDescription($value);
    }
}