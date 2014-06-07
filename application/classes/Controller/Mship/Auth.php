<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Mship_Auth extends Controller_Mship_Master {

    public function before() {
        parent::before();
    }

    /**
     * Allow the current user to login using their CID and password.
     */
    public function action_login() {
        // What's our return URL?
        if (!$this->request->query("return")) {
            $returnURL = "/mship/manage/display";
            if ($this->request->query("returnURL")) {
                $returnURL = urldecode($this->request->query("returnURL"));
            }
            $this->session()->set("return_url", $returnURL);
        }

        // Is this user already authenticated?
        if ($this->_current_account->loaded()) {
            $this->postAuthRedirect();
            return true;
        }

        // We're basically just going to go straight over VATSIM SSO.
        $SSO = Vatsim::factory("Sso");
        try {
            $details = $SSO->doRunSSO();
            $member = ORM::factory("Account", $details->user->id);
            $member->setSessionData();
        } catch (Exception $e) {
            // TODO: Log.
            $this->setMessage("Authentication Error", "There was an error authenticating you, please try again.", "error");
            $this->redirect("/error/generic/VATSIM_SSO_AUTH");
            return false;
        }

        $this->postAuthRedirect();
        return true;
    }

    public function action_logout() {
        if ($this->request->query("returnURL") != null && $this->request->query("ssoKey") != null) {
            $this->session()->set("sso_logout_url", $this->request->query("returnURL"));
        }

        // Submitted the form?
        if (HTTP_Request::POST == $this->request->method() || $this->request->query("override") == 1 || $this->request->query("ssoKey") == null) {
            // Run the logout!
            if ($this->request->post("processlogout") == 1 || $this->request->query("override") == 1 || $this->request->query("ssoKey") == null) {
                if ($this->_current_account->is_overriding()) {
                    $this->_current_account->override_disable();
                } else {
                    $this->_current_account->action_logout();
                    $this->_current_account->security->action_deauthorise();
                }
            }

            // Redirect?
            $redirectURL = $this->session()->get_once("sso_logout_url", "/mship/manage/display");
            $this->redirect($redirectURL);
            return;
        }

        // Add the key to the form.
        $this->_data["area"] = $this->request->query("ssoKey");
    }

    public function action_security_secondary() {
        // If this user isn't authenticated, send them on their merry way.
        if (!$this->_current_account->loaded()) {
            $this->redirect("/mship/manage/display");
            return false;
        }

        // Submitted the form?
        if (HTTP_Request::POST == $this->request->method()) {
            // Try and authenticate!
            $result = $this->_current_account->security->action_authorise($this->request->post("password"));
            if ($result) {
                $this->postAuthRedirect();
                return;
            } else {
                $this->setMessage("Security Authorisation Error", "The secondary password you entered was incorrect.", "error");
            }
        }
        $this->setTitle("Secondary Password");
    }

    /**
     * Handles the redirect beyond authentication.
     */
    public function postAuthRedirect() {
        // Check the secondary password - do we need to enter it?
        if ($this->_current_account->security->loaded()) {
            // Completel new password?
            if ($this->_current_account->security->value == NULL) {
                $this->redirect("/mship/security/replace");
                return true;
            } elseif (!$this->_current_account->security->is_active()) {
                $this->redirect("/mship/security/replace");
                return true;
            } elseif ($this->_current_account->security->require_authorisation()) {
                $this->redirect("/mship/auth/security_secondary");
                return true;
            }
        }
        $this->redirect("/mship/manage/display");
        die("NO PASSWORD");

        // Handle the main redirect - later!
        $returnURL = $this->session()->get_once("return_url");
        $this->session()->delete("auth_lock");
        $this->redirect($returnURL);
        return true;
    }

}
