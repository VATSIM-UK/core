<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Training_Theory_Difficulty extends Enum_Main {
    const BASIC = 10;
    const MEDIUM = 50;
    const DIFFICULT = 90;
    
    public static function getDescription($value){
         switch($value){
              case self::BASIC:
                   return 'Basic/Easy';
                   break;
              case self::MEDIUM:
                   return 'Medium';
                   break;
              case self::DIFFICULT:
                   return 'Difficult';
                   break;
              default:
                   return parent::getDescription($value);
         }
    }
    
}