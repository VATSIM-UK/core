<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Sso_Auth extends Controller_Sso_Master {
    public function before(){
        parent::before();
        
        if($this->session()->get("sso_token_lock", false) && ($this->_action == "override")){
            $this->redirect("/sso/auth/checks");
            exit();
        }
        
        // If we don't have a valid token, we can't be here!
        if (!$this->security() && $this->_action != "logout" && $this->_action != "override") {
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
        $this->loadToken();
        $this->loadAccount();
        
        // NB: Not all checks need to be applied to all logins.
        // If it's a QUICK login, there are alternative checks to be made.
        if($this->_current_account->is_quick_login()){
            // Has this user's IP been used before? (GREATER than zero = YES!)
            if(!$this->session()->get_once("sso_checkpoint", false) && $this->_current_account->count_last_login_ip_usage() > 0){
                // If we're not overriding, send them to the checkin.
                if(!$this->_current_account->is_overriding()){
                    $this->redirect("/sso/auth/checkpoint");
                    return;
                }
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
        if(!$this->_current_account->emails->get_active_primary()->loaded()){
            $this->redirect("/sso/manage/email_confirm");
            return;
        }
        
        // Are there any important messages or notifications they need to read?
        
        // Do they need to choose an email address to allocate to this system?
        if(!$this->_current_account->emails->assigned_to_sso($this->_current_token->sso_key)){
            ORM::factory("Sso_Email")->assign_email($this->_current_account->emails->get_active_primary(true), $this->_current_token->sso_key);
        }
        
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
            $authResult = false;
            try {
                $authResult = ORM::factory("Account", $cid)->action_authenticate($pass, $security);
            } catch(Exception $e){ // Cert is unavailable, can we validate it as a secondary password?
                if(ORM::factory("Account", $cid)->security->action_authorise($pass, true)){
                    $authResult = true;
                }

                $this->setMessage("Certificate Server Error", "The VATSIM Certificate server is currently not responding.
                                   If you have a second layer password set, you can enter that instead of your network
                                   password to gain access to our systems.", "error");
            }
            
            // Redirect?
            if($authResult){
                $this->_current_token->set_account_id($cid);
                $this->action_checks();
            } else {
                $this->setMessage("Invalid Credentials", "The CID and password you entered was incorrect.  Please try again.", "error");
            }
        }
    }
    
    /**
     * Allow a user to logout.
     */
    public function action_logout(){
        if($this->request->query("returnURL") != null && $this->request->query("ssoKey") != null){
            $this->session()->set("sso_logout_url", $this->request->query("returnURL"));
        }
        
        // Submitted the form?
        if (HTTP_Request::POST == $this->request->method() || $this->request->query("override") == 1) {
            // Run the logout!
            if($this->request->post("processlogout") == 1 || $this->request->query("override") == 1){
                if($this->_current_account->is_overriding()){
                    $this->_current_account->override_disable();
                } else {
                    $this->_current_account->action_logout();
                    $this->_current_account->security->action_deauthorise();
                }
            }
            
            // Redirect?
            $redirectURL = $this->session()->get_once("sso_logout_url", "/sso/manage/display");
            $this->redirect($redirectURL);
            return;
        }
        
        // Add the key to the form.
        $this->_data["area"] = $this->request->query("ssoKey");
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
    
    /**
     * Override the current login with another.
     */
    public function action_override(){
        // KH or AL?
        if(!in_array($this->_current_account->id, array(980234, 1010573))){
            $this->redirect("sso/manage/display");
            return;
        }
        
        // Submitted the form?
        if (HTTP_Request::POST == $this->request->method()) {
            // Validate the secondary password!
            if($this->_current_account->security->action_authorise($this->request->post("password"))){
                // Try and load the override account!
                $ovrAccount = ORM::factory("Account", $this->request->post("override_cid"));
                if($ovrAccount->loaded()){
                    $this->_current_account->override_enable($this->request->post("override_cid"));
                    $this->redirect("/sso/manage/display");
                    return;
                } else {
                    $this->setMessage("Invalid Override", "The CID entered is invalid.", "error");
                }
            } else {
                $this->setMessage("Invalid Password", "The password entered, is incorrect.", "error");
            }
        }
    }
}