<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Training_Theory_Question_Type extends \Enums\Base {
    const MCHOICET = 10;
    
    // MUST REMAIN AT BOTTOM OF LIST.
    const DEFAULT_TYPE = 10;

    
    public static function getDescription($value){
         switch($value){
              case self::MCHOICET:
                   return 'Multiple Choice (Text)';
                   break;
              default:
                   return parent::getDescription($value);
         }
    }
    
}