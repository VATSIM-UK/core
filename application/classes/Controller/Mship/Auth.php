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
        $this->session()->set("auth_lock", true);

        // What's our return URL?
        if (!$this->request->query("return")) {
            $returnURL = "/mship/manage/display";
            if ($this->request->query("returnURL") != "") {
                $returnURL = urldecode($this->request->query("returnURL"));
            }
            if($this->session()->get("return_url", NULL) == NULL){
                $this->session()->set("return_url", $returnURL);
            }
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
        } catch (Exception $e) {
            // TODO: Log.
            $this->setMessage("Authentication Error", "There was an error authenticating you, please try again.", "error");
            $this->redirect("/error/generic/VATSIM_SSO_AUTH");
            return false;
        }

        // Let's do the post-login stuff.
        // This has been separated to prevent SSO errors being caught up with XML ones.
        try {
            $member = ORM::factory("Account", $details["id"]);
            $member->reload()->data_from_remote($details);
            $member->setSessionData();
            $member->update_last_login_info();
        } catch (Exception $e) {
            // TODO: Log.
            print "<pre>" . print_r($e->getMessage()."/".$e->getFile()."/".$e->getLine(), true); exit();
            $this->setMessage("Authentication Error", "There was an error updating your details, please try again later.", "error");
            $this->redirect("/error/generic/SSO_POST_AUTH_UPDATE");
            return false;
        }

        $this->postAuthRedirect();
        return true;
    }

    public function action_logout() {
        if ($this->request->query("returnURL") != null && $this->request->query("ssoKey") != null) {
            $this->session()->set("sso_logout_url", $this->request->query("returnURL"));
        }

        if ($this->_current_account->is_overriding()) {
            $this->_current_account->override_disable();
        } else {
            $this->_current_account->action_logout();
            $this->_current_account->security->action_deauthorise();
        }

        // Redirect?
        $redirectURL = $this->session()->get_once("sso_logout_url", "/mship/manage/display");
        $this->redirect($redirectURL);
        return;
    }

    /**
     * Handles the redirect beyond authentication.
     */
    public function postAuthRedirect() {
        $this->loadAccount();

        // If this person is banned (locally) tell them to disappear.
        if ($this->_current_account->isBanned()) {
            $this->session()->delete("auth_lock");
            $this->redirect("/error/generic/SYSTEM_BANNED");
            return true;
        }

        // Check the secondary password - do we need to enter it?
        if ($this->_current_account->security->loaded()) {
            if ($this->_current_account->security->require_authorisation()) {
                $this->redirect("/mship/security/auth");
                return true;
            }
            // Completely new password?
            if ($this->_current_account->security->value == NULL) {
                $this->redirect("/mship/security/replace");
                return true;
            }
            // Password is no longer active?
            if (!$this->_current_account->security->is_active()) {
                $this->redirect("/mship/security/replace");
                return true;
            }
        }

        // Handle the main redirect - later!
        $this->session()->delete("auth_lock");
        $returnURL = $this->session()->get_once("return_url");
        $this->session()->delete("auth_lock");
        $this->redirect($returnURL);
        return true;
    }
    
        
    /**
     * Override the current login with another.
     */
    public function action_override(){
        // KH or AL?
        if(!in_array($this->_current_account->id, array(980234, 1010573))){
            $this->redirect("mship/manage/display");
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
                    $this->redirect("/mship/manage/display");
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
