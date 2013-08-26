<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Sso_Auth extends Controller_Sso_Master {

    /**
     * Allow the current user to login using their CID and password.
     */
    public function action_login() {
        // If we don't have a valid token, we can't be here!
        if (!$this->_current_token->loaded()) {
            $this->redirect("sso/error?e=token&r=SSO_AUTH_LOGIN");
            exit();
        }

        if (HTTP_Request::POST == $this->request->method()) {
            // Let's gather the CID and password
            $cid = $this->request->post("cid");
            $pass = $this->request->post("password");
            $security = $this->request->post("security");

            // Try and authenticate!
            $authResult = ORM::factory("Account", $cid)->action_authenticate($pass, $security);
            var_dump($authResult);
            die("==end==");
        }
    }
}