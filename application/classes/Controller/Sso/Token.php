<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Sso_Token extends Controller_Sso_Master {

    public function action_auth() {
        // Get the necessary information.
        $token = $this->request->query("token", null);
        $key = $this->request->query("ssoKey", null);
        $returnURL = $this->request->query("returnURL");

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
}