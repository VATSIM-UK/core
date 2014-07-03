<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Training_Master extends Controller_Master {
    protected $_templateDir = "Standalone"; // Override parent settings.
    
    public function before() {
        parent::before();
        
        // If they're not logged in, send them to the SSO page to login!
        if(!$this->_current_account OR !$this->_current_account->loaded()){
            require_once "/var/www/sharedResources/SSO.class.php";
            $SSO = new SSO("CORE", URL::site("/training/".$this->_controller."/".$this->_action, "http"), false, URL::site("/sso/token/auth"));
            $details = $SSO->member;
        }
    }
}