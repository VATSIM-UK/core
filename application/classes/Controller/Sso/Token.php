<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Sso_Token extends Controller_Sso_Master {
    public function action_auth() {
        // Get the necessary information.
        $token = $this->request->query("token", null);
        $key = $this->request->query("ssoKey", null);
        
        // Now check the token file and get the returnURL.
        if(!ORM::factory("Sso_Token")->check_token_file($token)){
            return false;
        }
        $returnURL = ORM::factory("Sso_Token")->get_token_file($token);
        
        // Let's sort the storing of this session out!
        try {
            $token = ORM::factory("Sso_Token")->set_current_token($token, $key, $returnURL);
        } catch (Exception $e) {
            $this->redirect("sso/error?e=token&r=SSO_TOKEN_AUTH");
            return;
        }

        // Since we've now set the session in the database, we can start the login process!
        $this->redirect("sso/auth/login");
        return;
    }
    
    /**
     * Redirect the user to either the expected location or internally to SSO.
     * 
     * @param boolean $forceInternal If set to TRUE, will always use an internal request.
     * @return void
     */
    public function action_redirect($forceInternal=false){
        if(!$this->security()){
            $this->redirect("sso/error?e=token&r=SSO_TOKEN_SECURITY");
            return;
        }
        
        $internal = ($forceInternal === true);
        $redirect = "/sso/manage/display";
        
        if(!$internal){
            $redirect = $this->_current_token->return_url;
        }
        
        $this->redirect($redirect);
    }
}