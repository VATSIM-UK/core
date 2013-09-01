<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Sso_Auth extends Controller_Sso_Master {
    public function before(){
        parent::before();
        
        // If we don't have a valid token, we can't be here!
        if (!$this->security()) {
            $this->redirect("sso/error?e=TOKEN&r=SSO_AUTH_".strtoupper($this->_action));
            exit();
        }

    }
    
    /**
     * Make a few checks and redirect the user to the necessary places.
     * 
     * This should always be run after positive responses from functions within here.
     */
    public function action_checks(){
        // NB: Not all checks need to be applied to all logins.
        // If it's a QUICK login, there are alternative checks to be made.
        if($this->_current_account->is_quick_login()){
            // Has this user's IP been used before? (GREATER than zero = YES!)
            if(!$this->session()->get_once("sso_checkpoint", false) && $this->_current_account->count_last_login_ip_usage() > 0){
                $this->redirect("/sso/auth/checkpoint");
                return;
            } else {
                // Do we need to authenticate their second password?
                if($this->_current_account->security->loaded() && $this->_current_account->security->require_validation()){
                    $this->redirect("/sso/security/auth");
                    return;
                }
            }
        } else {
            // Do we need to authenticate their second password?
            if($this->_current_account->security->loaded() && $this->_current_account->security->require_validation()){
                $this->redirect("/sso/security/auth");
                return;
            }
        }
        
        // Does their second layer security need updating?
        if(!$this->_current_account->security->is_active()){
            $this->redirect("/sso/security/replace");
            return;
        }
        
        // Do we need to validate their primary email address?
        
        // Are there any important messages or notifications they need to read?
        
        // Do they need to choose an email address to allocate to this system?
        
        // Let's continue! We'll return to the token form, for this.
        $this->redirect("/sso/token/redirect");
        return;
    }
    
    /**
     * Allow the current user to login using their CID and password.
     */
    public function action_login() {
        // Is this user already authenticated?
        if($this->_current_account->loaded()){
            $this->_current_account->action_quick_login();
            $this->_current_token->set_account_id($this->_current_account->id);
            $this->action_checks();
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
                $this->_current_token->set_account_id($cid);
            
                // Now, we need to run the checks and send them to the necessary place!
                $this->action_checks();
            } catch(Exception $e){
                $this->setMessage("Certificate Server Error", "The VATSIM Certificate server is currently not responding.
                                   If you have a second layer password set, you can enter that instead of your network
                                   password to gain access to our systems.", "error");
            }
        }
    }
    
    /**
     * Allow a user to logout.
     */
    public function action_logout(){
        
    }
    
    /**
     * Security checkpoint for multiple uses of the same login IP.
     */
    public function action_checkpoint(){
        $this->_data["has_sls"] = $this->_current_account->security->loaded();
        
        // Submitted the form?
        if (HTTP_Request::POST == $this->request->method()) {
            // SLS?
            if($this->_data["has_sls"]){
                $result = $this->_current_account->security->action_authorise($this->request->post("password"));
            } else { // NORMAL Login.
                $result = $this->_current_account->validate_password($this->request->post("password"));
            }
            
            // RESULT!
            if($result){
                $this->session()->set("sso_checkpoint", true);
                $this->action_checks();
            } else {
                $this->setMessage("Checkpoint Error", "The details you entered in response to the verification question are invalid.  Please try again.", "error");
            }
        }
    }
}