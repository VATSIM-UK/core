<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Sso_Master extends Controller_Master {
    protected $_templateDir = "Standalone"; // Override parent settings.

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
        
        if(!is_object($this->_current_token)){
            $this->session()->delete("sso_security_grace");
            die("HERE!1");
            return false;
        }
        if(!$this->_current_token->loaded()){
            $this->session()->delete("sso_security_grace");
            die("HERE!2");
            return false;
        }
        if(strtotime($this->_current_token->expires) < strtotime(gmdate("Y-m-d H:i:s"))){
            $this->session()->delete("sso_security_grace");
            die("HERE!3");
            return false;
        }
        if($checkAccount === true && !is_object($this->_current_account) && !$this->_current_account->loaded()){
            return false;
        }
        return true;
    }
}