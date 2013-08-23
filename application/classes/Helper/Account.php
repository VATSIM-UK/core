<?php

defined('SYSPATH') or die('No direct script access.');

class Helper_Account {
    /**
     * Update the account using the remote VATSIM feeds.
     * 
     * @param int $account_id The account_ID we're creating/updating.
     * @return boolean True on success, false otherwise.
     */
    public static function update_using_remote($account_id){
        // Got a user to do this on?
        //if($account_id == Kohana::$config->load("general")->get("system_user") || $account_id == null || !is_numeric($account_id)){
        if($account_id == null || !is_numeric($account_id)){
            return false;
        }
        
        // Let's load the data
        /*$account = ORM::factory("Account_Main", $account_id);
        
        // Now get all of the details from VATSIM
        try {
            // Details from remote.
            $details = Vatsim::factory("autotools")->getInfo($account_id);
        
            // Valid?
            if(!is_array($details) || count($details) < 1){
                return false;
            }
            
            // Let's now run the updates!
            Helper_Account_Main::run_update($account_id, $details, !$account->loaded());
        } catch(Exception $e){
            // TODO: Handle this!
            return false;
        }*/
        return true;
    }
}

?>