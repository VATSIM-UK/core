<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Sso_Auth extends Controller_Sso_Master {
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