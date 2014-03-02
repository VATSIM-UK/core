<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Training_Theory_Question_Type extends Enum_Main {
    const CHOICE = 10;
    
    public static function getResult($value){
         switch($value){
              case self::CHOICE:
                   return 'Multiple Choice';
                   break;
              default:
                   return parent::getDescription($value);
         }
    }
    
}