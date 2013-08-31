<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Sso_Security extends Controller_Sso_Master {
    public function before(){
        parent::before();
        
        // If we don't have a valid token, we can't be here!
        if (!$this->security()) {
            $this->redirect("sso/error?e=TOKEN&r=SSO_SECURITY_".strtoupper($this->_action));
            exit();
        }

    }
    
    /**
     * Authorise the user's access with their secondary password.
     */
    public function action_auth() {
        // Submitted the form?
        if (HTTP_Request::POST == $this->request->method()) {
            // Try and authenticate!
            $result = $this->_current_account->security->action_authorise($this->request->post("password"));
            if($result){
                $this->redirect("/sso/auth/checks");
                return;
            } else {
                $this->setMessage("Security Authorisation Error", "The secondary password you entered was incorrect.", "error");
            }
        }
        $this->setTitle("Secondary Password");
    }
    
    /**
     * Authorise the user's access with their secondary password.
     */
    public function action_replace() {
        // Submitted the form?
        if (HTTP_Request::POST == $this->request->method()) {
            // Try and authenticate the old password
            if($this->_current_account->security->value == null){
                $result = true;
            } else {
                $result = $this->_current_account->security->action_authorise($this->request->post("old_password"));
            }
            
            // Continue?
            if($result){
                // Now we can set the new one!
                if($this->request->post("new_password") != $this->request->post("new_password2")){
                    $this->setMessage("Security Authorisation Error", "The two new passwords do not match, please try again.", "error");
                } elseif($this->_current_account->security->hash($this->request->post("new_password")) == $this->_current_account->security->value){
                    $this->setMessage("Security Authorisation Error", "Your new password cannot be the same as your old password.", "error");
                } else {
                    // Update!
                    $security = ORM::factory("Account_Security");
                    $security->account_id = $this->_current_account;
                    $security->type = $this->_current_account->security->type;
                    $security->value = $this->request->post("new_password");
                    $security->created = gmdate("Y-m-d H:i:s");
                    $this->_current_account->security->delete();
                    $security->save();
                    $security->action_authorise($this->request->post("new_password"), true);
                    
                    // Send back and do some more checks!
                    $this->redirect("/sso/auth/checks");
                    return;
                }
            } else {
                $this->setMessage("Security Authorisation Error", "The existing secondary password you entered was incorrect.", "error");
            }
        }
        
        // What are the requirements?
        $enum = "Enum_Account_Security_".ucfirst(strtolower(Enum_Account_Security::valueToType($this->_current_account->security->type)));
        $requirements = array();

        if($enum::MIN_LENGTH > 0){
            $requirements[] = "A minimum length of ".$enum::MIN_LENGTH;
        }
        if($enum::MIN_ALPHA > 0){
            $requirements[] = "Contain a minimum of ".$enum::MIN_ALPHA." alphabetical (A-Z) characters.";
        }
        if($enum::MIN_NUMERIC > 0){
            $requirements[] = "Contain a minimum of ".$enum::MIN_NUMERIC." numeric (0-9) digits.";
        }
        if($enum::MIN_NON_ALPHANUM > 0){
            $requirements[] = "Contain a minimum of ".$enum::MIN_NON_ALPHANUM." none alpha-numeric characters, for example !)(><.,";
        }
        $this->_data["_requirements"] = $requirements;
        
        if($this->_current_account->security->value == null){
            $this->setTitle("Set Secondary Password");
            $this->_data["sls_type"] = "forced";
        } else {
            $this->setTitle("Secondary Password Expired");
            $this->_data["sls_type"] = "expired";
        }
    }
}