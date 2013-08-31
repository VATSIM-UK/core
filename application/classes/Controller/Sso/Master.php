<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Sso_Master extends Controller_Master {
    protected $_templateDir = "Standalone"; // Override parent settings.
    protected $_current_token = null;
    protected $_current_account = null;
    protected $_actual_account = null;
    
    protected function loadAccount(){
        $this->_current_account = ORM::factory("Account_Main")->get_current_account();
    }
    
    protected function loadToken(){
        $this->_current_token = ORM::factory("Sso_Token")->get_current_token();
    }

    public function __construct($request, $response){
        parent::__construct($request, $response);
        
        // Load things!
        $this->loadAccount();
        $this->loadToken();
        
    }
    
    /**
     * Run the security checks.
     * 
     * @param type $checkAccount If TRUE a check will be made for a valid account, too.
     * @return boolean TRUE if no security issues.  FALSE otherwise.
     */
    protected function security($checkAccount=false){
        // Reload the data!
        $this->loadAccount();   
        $this->loadToken();
        
        if(!is_object($this->_current_token) || !$this->_current_token->loaded() || strtotime($this->_current_token->expires) < time()){
            return false;
        }
        if($checkAccount === true && !is_object($this->_current_account) && !$this->_current_account->loaded()){
            return false;
        }
        return true;
    }
}