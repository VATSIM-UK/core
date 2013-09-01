<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Sso_Manage extends Controller_Sso_Master {
    /**
     * Display the user's details if they're logged in.
     * 
     * If a user is not logged in, send them to the SSO system to login.
     */
    public function action_display(){
        // If they're not logged in, we'll treat this as an SSO login.
        if(!$this->_current_account->loaded()){
            require_once "/var/www/sharedResources/SSO.class.php";
            $SSO = new SSO("CORE", URL::site("/sso/manage/display", "http"), false, "http://dev.vatsim-uk.co.uk/ALawrence/core.vatsim-uk.co.uk/sso/token/auth");
            $details = $SSO->member;
        }
        
        // Set the account details
        $this->_data["_account"] = $this->_current_account;
    }
    
    public function action_email_confirm(){
        
    }
    
    public function action_email_allocate(){
        
    }
    
}