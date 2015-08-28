<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Training_Theory_Result extends \Enums\Base {
    const STARTED = 10;
    const CANCEL = 20;
    const MARKING = 40;
    const REVIEW = 50;
    const PASS = 80;
    const FAIL = 90;
    
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
              case self::MARKING:
                   return 'Pending Marking';
                   break;
              case self::REVIEW:
                   return 'Under Review';
                   break;
              default:
                   return parent::getDescription($value);
         }
    }
    
}