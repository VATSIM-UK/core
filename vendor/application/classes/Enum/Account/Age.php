<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Account_Age extends Enum_Main {
    const _0 = 0;
    const _13 = 1;
    const _1317 = 2;
    const _1825 = 3;
    const _2636 = 4;
    const _3748 = 5;
    const _4960 = 6;
    const _60 = 7;
    
    public static function getDescription($id){
        switch(self::idToType($id)){
            case "_13":
                return "Under 13";
            case "_1317":
                return "13 - 17";
            case "_1825":
                return "18 - 25";
            case "_2636":
                return "26 - 36";
            case "_3748":
                return "37 - 48";
            case "_4960":
                return "49 - 60";
            case "_60":
                return "Over 60";
            default:
                return "unknown";
        }
    }
}