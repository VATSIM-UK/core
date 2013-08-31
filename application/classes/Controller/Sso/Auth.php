<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Sso_Auth extends Controller_Sso_Master {
    /**
     * Make a few checks and redirect the user to the necessary places.
     * 
     * This should always be run after positive responses from functions within here.
     */
    private function checks(){
        // If we don't have a valid token, or account, we can't be here!
        if (!$this->security()) {
            $this->redirect("sso/error?e=token&r=SSO_AUTH_LOGIN");
            exit();
        }
        
        // NB: Not all checks need to be applied to all logins.
        // If it's a QUICK login, there are alternative checks to be made.
        if($this->_current_account->is_quick_login()){
            // Has this user's IP been used before?
        } else {
            // Do we need to authenticate their second password?
        }
        
        // Do we need to validate their primary email address?
        
        // Are there any important messages or notifications they need to read?
        
        // Do they need to choose an email address to allocate to this system?
        
        // Let's continue! We'll return to the token form, for this.
        $this->redirect("/sso/token/redirect");
    }
    
    /**
     * Allow the current user to login using their CID and password.
     */
    public function action_login() {
        // If we don't have a valid token, we can't be here!
        if (!$this->security()) {
            $this->redirect("sso/error?e=token&r=SSO_AUTH_LOGIN");
            exit();
        }
        
        // Is this user already authenticated?
        if($this->_current_account->loaded()){
            $this->_current_account->action_quick_login();
            $this->checks();
            return;
        }
        
        // Submitted the form?
        if (HTTP_Request::POST == $this->request->method()) {
            // Let's gather the CID and password
            $cid = $this->request->post("cid");
            $pass = $this->request->post("password");
            $security = $this->request->post("security");

            // Try and authenticate!
            try {
                $authResult = ORM::factory("Account", $cid)->action_authenticate($pass, $security);
            } catch(Exception $e){
                $this->setMessage("Certificate Server Error", "The VATSIM Certificate server is currently not responding.
                                   If you have a second layer password set, you can enter that instead of your network
                                   password to gain access to our systems.", "error");
            }
            
            // Now, we need to run the checks and send them to the necessary place!
            $this->checks();
        }
    }
}