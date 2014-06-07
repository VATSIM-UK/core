<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Sso_Auth extends Controller_Sso_Master {
    public function before(){
        parent::before();
        }
    
    /**
     * Allow the current user to login using their CID and password.
     */
    public function action_login(){
        // Did we receive a token?  If we did, is it calculated correctly?
        if(!$this->request->query("token")){
            $this->redirect("/error/generic/SSO_AUTH_NO_TOKEN");
            return false;
        }
        
        // Check expired/valid.
        $ssoToken = ORM::factory("Sso_Token")->locate($this->request->query("token"));
        if(!$ssoToken->loaded() OR $ssoToken->isExpired()){
            $this->redirect("/error/generic/SSO_AUTH_INV_TOKEN");
            return false;
        }
        
        // Send them away!
        if(!$this->request->query("return")){
            // Let's extend the expiry of this token for a minute or two....
            $ssoToken->expires = gmdate("Y-m-d H:i:s", strtotime("+3 minutes"));
            $ssoToken->save();
            
            // Now let's send them off to the login shizzle!
            $this->redirect("/mship/auth/login?returnURL=".urlencode(URL::site("/sso/auth/login?token=".$this->request->query("token")."&return=1")));
        } else {
            // We're successfully authenticated it seems... We can now return the access token.
            $ssoAccessToken = ORM::factory("Sso_Token");
            $ssoAccessToken->token = sha1($ssoToken->token . md5($ssoToken->account->api_key_private));
            $ssoAccessToken->sso_account_id = $ssoToken->sso_account_id;
            $ssoAccessToken->account_id = $this->_current_account->id;
            $ssoAccessToken->created = gmdate("Y-m-d H:i:s");
            $ssoAccessToken->expires = gmdate("Y-m-d H:i:s", strtotime("+30 seconds"));
            $ssoAccessToken->save();
            
            $this->redirect($ssoToken->return_url);
            return true;
        }
    }
}