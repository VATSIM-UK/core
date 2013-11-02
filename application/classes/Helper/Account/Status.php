<?php 

defined('SYSPATH') or die('No direct script access.');

class Helper_Account_Status {
    /**
     * Determine the current status flags of the given member.
     * 
     * @param int|object The current user (or ID).
     * @return array An array of status flags.
     */
    public static function getStatusFlags($member){
        if(!is_object($member)){
            $member = ORM::factory("Account_Main", $member);
            if(!is_object($member) OR !$member->loaded()){
                return array();
            }
        }
        
        // Now, sort out the status!
        $return = array();
        foreach(Enum_Account_Status::getAll() as $key => $value){
            $return[strtolower($key)] = (int) (boolean) (bindec($value) & $member->status);
        }
        return $return;
    }
}