<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Sso_Auth extends Controller_Sso_Master {
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
                        die("AKMD".__LINE__);
            // Has this user's IP been used before? (GREATER than zero = YES!)
            if(!$this->session()->get_once("sso_checkpoint", false) && $this->_current_account->count_last_login_ip_usage() > 0){
                // If we're not overriding, send them back to the checkpoint to confirm.
                if(!$this->_current_account->is_overriding()){
                        die("AKMD".__LINE__);
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
            //$this->redirect("/sso/manage/email_confirm");
            //return;
        }
        
        // Are there any important messages or notifications they need to read?
        // TODO: HERE!
        
        // Do they need to choose an email address to allocate to this system?
        if(!$this->_current_account->emails->assigned_to_sso($this->_current_token->sso_key)){
            ORM::factory("Sso_Email")->assign_email($this->_current_account->emails->get_active_primary(true), $this->_current_token->sso_key);
        }
        
        // Let's continue! We'll return to the token form, for this.
        $this->redirect("/sso/token/redirect");
        return;
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