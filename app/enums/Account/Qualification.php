<?php

namespace Enums\Account;

class Qualification extends \Enums\Base {
    const UNKNOWN = 0;
    
    public static function getDescription($value){
        return parent::valueToKey($value);
    }
}