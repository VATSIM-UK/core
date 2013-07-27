<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Account_Session extends Controller_Account_Master {

    protected $_permissions = array(
        "_" => array('*'),
    );

    public function getDefaultAction() {
        return "login";
    }

    public function before() {
        parent::before();
    }

    public function after() {
        parent::after();
    }

    /*public function action_nowhere() {
        Minion_Task::factory(array("task" => "Cert_Memberdownload", "debug" => true))->execute();
        print View::factory("profiler/stats");
    }*/

    public function action_logout() {
        // Action?
        $action = Arr::get($_GET, "action", URL::site("account/nowhere"));

        // Delete cookies and sessions.
        Cookie::delete("vuk_sso_cid");
        Cookie::delete("vuk_sso_auth");
        Session::instance()->destroy();

        // Send away
        $this->redirect($action);
    }

    public function action_login() {
        // Determine the action to take after the login has been handled.
        $account = ORM::factory("Account");
        if (Arr::get($_GET, "action", null) != null) {
            $action = Arr::get($_GET, "action", "account/nowhere");
            $nonce = Arr::get($_GET, "nonce", Text::random("alnum", 15));

            // Store these temporarily in session
            Session::instance()->set("tmp_action", $action);
            Session::instance()->set("tmp_nonce", $nonce);

            // Now, "refresh" and remove them from sight!
            $this->redirect("account/session/login");
            return;
        } elseif (Session::instance()->get("tmp_action", null) != null) {
            $action = Session::instance()->get("tmp_action");
            $nonce = Session::instance()->get("tmp_nonce");
        } else {
             
            $action = URL::site("account/manage/dashboard");
            $nonce = md5(uniqid());
        }

        // Is a cookie set?
        if (Cookie::decrypt("vuk_sso_cid", null) != null && Cookie::decrypt("vuk_sso_auth", null) != null) {
            // Get the value & the account.
            $account = ORM::factory("Account", Cookie::get("vuk_sso_cid"));
            $token = Cookie::decrypt("vuk_sso_auth");

            // Check the token - if valid, update the token and send away.
            $expected = md5($account->token . $account->token_ip . $account->id);
            if ($token == $expected) {
                // Update security tokens.
                $account->token = md5(uniqid("VUK-", true));
                $account->token_ip = ip2long($_SERVER["REMOTE_ADDR"]);
                $account->save();

                // Update user's cookie
                Cookie::encrypt("vuk_sso_cid", $account->id, (60 * 60 * 24));
                Cookie::encrypt("vuk_sso_auth", md5($account->token . $account->token_ip . $account->id), (60 * 60 * 24));
            }
        }


        // If the user has submitted details, let's process it.
        if (HTTP_Request::POST == $this->request->method()) {
            $result = $this->process_login();
            if ($result === true) {
                // Let's create the response.
                $response = md5($nonce . md5($_SERVER["REMOTE_ADDR"]) . $nonce);
                $cid = Session::instance()->get(Kohana::$config->load('general')->get("session_name"));

                // Get the account details
                $account = ORM::factory("Account", $cid);

                // Update security tokens.
                $account->token = md5(uniqid("VUK-", true));
                $account->token_ip = $_SERVER["REMOTE_ADDR"];
                $account->save();

                // Update user's cookie
                Cookie::encrypt("vuk_sso_cid", $account->id, (60 * 60 * 24));
                Cookie::encrypt("vuk_sso_auth", md5($account->token . $account->token_ip . $account->id), (60 * 60 * 24));
            } else {
                // What response did we get?
                if(strcasecmp($result, "BANNED_NETWORK") == 0){
                    Session::instance()->set("errors", Session::instance()->get("errors", array()) + array("You are currently banned from the network, please try to login when your ban has been lifted."));
                } elseif(strcasecmp($result, "BANNED_SYSTEM") == 0){
                    Session::instance()->set("errors", Session::instance()->get("errors", array()) + array("You are currently banned from our services, please try to login when your ban has been lifted."));
                } else {
                    Session::instance()->set("errors", Session::instance()->get("errors", array()) + array("The details you have supplied are invalid, please check and try again."));
                }
            }
        }
        
        /***************** SEND BACK *********/
        if($account->loaded()){
            // Do we need to get an email address from them?
            if($account->emails->where("deleted", "IS", NULL)->count_all() < 1){
                $this->redirect("account/manage/email_confirm");
                return;
            }
            
            // What about an extra password?
            if($account->extra_password != ''){
                $this->redirect("account/session/extra_security");
                return;
            }
            
            // Process the login redirection
            $this->redirect("account/session/login_redirect");
        }

        /** TEMPLATE SETTINGS * */
        $this->setTemplate("Session/Login");
    }
    
    public function action_extra_security(){
        if (HTTP_Request::POST == $this->request->method()) {
            // Get the confirmation.
            $confirmed = $this->process_extra_security();
            
            // If it's confirmed, store and redirect!
            if($confirmed){
                // Redirect!
                $this->redirect("account/session/login_redirect");
                exit();
            }
            
            // It's not confirmed!!!! ERROR!!!!
            Session::instance()->set("errors", Session::instance()->get("errors", array()) + array("The extra security details you provided were invalid, please try again."));
        }

        /** TEMPLATE SETTINGS * */
        $this->setTemplate("Session/Extra_Security");
    }

    protected function process_extra_security() {
        // Firstly, are we logged in?
        if(!$this->_account->loaded()){
            $this->redirect();
            exit();
        }
        
        // Now, let's check the password against the one in the database!
        return sha1($this->request->post("extra_password")) == $this->_account->extra_password;;
    }
    
    public function action_login_redirect(){
        // Get the action and the nonce.
        $action = Session::instance()->get("tmp_action", null);
        $nonce = Session::instance()->get("tmp_nonce", null);
        
        // Let's create the response.
        $response = md5($nonce . md5($_SERVER["REMOTE_ADDR"]) . $nonce);

        // Destory sessions!
        Session::instance()->delete("tmp_action");
        Session::instance()->delete("tmp_nonce");

        // Redirect!
        $account = ORM::factory("Account", $this->_account->id);
        $return = Encrypt::instance("tripledes")->encode($account->id).",";
        $return.= Encrypt::instance("tripledes")->encode($account->name_first).",";
        $return.= Encrypt::instance("tripledes")->encode($account->name_last).",";
        $return.= Encrypt::instance("tripledes")->encode($account->emails->where("primary", "=", 1)->where("deleted", "IS", NULL)->find());
        $this->redirect($action . "?response=" . $response . "&response2=".Encrypt::instance("tripledes")->encode($return));
        return;
    }

    protected function process_login() {
        // Let's perform a check against CERT
        $cert = Vatsim::factory("autotools")->authenticate($this->request->post("cid"), $this->request->post("password"));
        if (!$cert) {
            return false;
        }
        
        // Member exists on VATSIM!
        
        // Get this user account.
        $account = ORM::factory("Account", $this->request->post("cid"));

        // Not loaded (account doesn't exist) - create a guest one for them.
        if (!$account->loaded()) {
            // Let's get some details from CERT for them.
            $details = Vatsim::factory("autotools")->getInfo($this->request->post("cid"));
            
            // Use the helper to create the member.
            Helper_Membership_Account::processMember(array("cid" => $this->request->post("cid"),
                                                           "name_first" => $details["name_first"],
                                                           "name_last" => $details["name_first"],
                                                           "created" => $details["regdate"],
                                                           "rating" => $details["rating"],
                                                           "prating" => $details["pilotrating"],
                                                           "location_country" => $details["country"],
                                                           "state" => Enum_Account_State::GUEST), Helper_Membership_Account::ACTION_USER);
            
            // Retry the login
            $result = $this->process_login();
            if($result !== false){
                return $result;
            }
            return false; // Failed second process login call.
        } else {
            // It's a valid request! Let's get the latest details for them.
            $details = Vatsim::factory("autotools")->getInfo($account->id);
            if (count($details) > 0) {
                $account->name_first = $details["name_first"];
                $account->name_last = $details["name_last"];
                $account->checked = gmdate("Y-m-d H:i:s");
                $account->save();
            }
        }
        
        // Are they banned?
        if(($account->status & bindec(Enum_Account::STATUS_SYSTEM_BANNED)) == true){
            return "BANNED_SYSTEM";
        }

        if(($account->status & bindec(Enum_Account::STATUS_NETWORK_BANNED)) == true){
            return "BANNED_NETWORK";
        }

        // Let's set a session!
        Session::instance()->set(Kohana::$config->load('general')->get("session_name"), $account->id);

        // Return!
        return true;
    }

}