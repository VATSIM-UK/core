<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Account_Note_Type extends Enum_Account_Note {
    const ACTION = 10; // Requires some type of action, will have a flag too.
    const IMPORTANT = 20; // "Stick" to the top of the page.
    const STANDARD = 30; // No defined category, generic text comment.
        const CUSTOM = 30; // Same as above, alias.
    const AUTO = 99; // Automatic notes from within the system.
        const SYSTEM = 99; // Same as above, alias.
    
    public static function getDescription($id){
        switch($id){
            case self::ACTION:
                return "Requires action";
            case self::IMPORTANT:
                return "Important/Sticky";
            case self::CUSTOM:
            case self::STANDARD:
                return "Standard Entry";
            case self::AUTO:
            case self::SYSTEM:
                return "System Comment";
            default:
                 return self::valueToType($id);
        }
    }
}
     

?>
