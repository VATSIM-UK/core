<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Account_Age extends \Enums\Base {
    const _0 = 0;
    const _13 = 1;
    const _1317 = 2;
    const _1825 = 3;
    const _2636 = 4;
    const _3748 = 5;
    const _4960 = 6;
    const _60 = 7;
    
    public static function getDescription($value){
        switch(self::valueToKey($value)){
            case self::_13:
                return "Under 13";
            case self::_1317:
                return "13 - 17";
            case self::_1825:
                return "18 - 25";
            case self::_2636:
                return "26 - 36";
            case self::_3748:
                return "37 - 48";
            case self::_4960:
                return "49 - 60";
            case self::_60:
                return "Over 60";
            default:
                return parent::getDescription($value);
        }
    }
}