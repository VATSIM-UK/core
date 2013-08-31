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
    }
}