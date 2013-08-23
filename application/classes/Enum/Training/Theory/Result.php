<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Training_Theory_Result extends Enum_Main {
    const STARTED = 0;
    const PASS = 1;
    const FAIL = 2;
    const CANCEL = 3;
    
    public static function getResult($value){
         switch($value){
              case self::PASS:
                   return 'Pass';
                   break;
              case self::FAIL:
                   return 'Fail';
                   break;
              case self::CANCEL:
                   return 'Forfeit';
                   break;
              case self::STARTED:
                   return 'In Progress';
                   break;
              default:
                   return parent::getDescription($value);
         }
    }
    
}