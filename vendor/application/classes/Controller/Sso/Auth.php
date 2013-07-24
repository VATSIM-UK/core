<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Sso_Auth extends Controller_Sso_Master {

    protected $_permissions = array(
        "_" => array('*'),
    );
    
    private $_current_token = null;
    private $_current_account = null;

    public function getDefaultAction() {
        return "login";
    }

    public function before() {
        parent::before();
    }

    public function after() {
        parent::after();
    }
    
    public function action_requestToken(){
        // For the request, we expect an API code to be issued.
        $APICodes = array("RTS_System" => "GyNeypTxZ5GomLlJyBkiGOUF",
                          "ANT_DEV" => "ajk5nSJn5lsmfnsaDLsn5",
                          "TS_REG" => "vmbFQVWQ6ycxdghEXooWB1rV",
                          "EVENTS_HUB" => "0EXXwGENxoPU1TfnFicDNG6H",
                          "HELPDESK" => "hVyTLne7XrH9av3HO9mr0HMT",
                          "FORUM" => "XDwLN7AlXoeqO6gnHcnqcZdx");
        
        // If no API code has been submitted, error.
        if(!array_key_exists($this->request->query("ssoKey"), $APICodes) && $APICodes[$this->request->query("ssoKey")] != $this->request->query("ssoSecret")){
            $this->response->body(json_encode(array("error" => "Invalid key/secret provided.")));
            return;
        }
        
        // No returnURL?
        if($this->request->query("returnURL") == null){
            $this->response->body(json_encode(array("error" => "You must submit a return URL with your inital token request.")));
            return;
        }
        
        // Now let's create a new request
        $request = ORM::factory("Sso_Token");
        $request->sso_key = $this->request->query("ssoKey");
        $request->created = gmdate("Y-m-d H:i:s");
        $request->expires = gmdate("Y-m-d H:i:s", strtotime("+7 minutes"));
        $request->return_url = $this->request->query("returnURL");
        $request->token = sha1($request->created.$this->request->query("ssoSecret").$request->expires).".".uniqid();
        $request->save();
        
        // Now, return the token
        $this->response->body(json_encode(array("token" => $request->token)));
        return;
    }
    
    public function action_requestDetails(){
        if(!$this->security() || $this->_current_token->account_id < 1){
            $this->response->body(json_encode(array("error" => "Invalid token supplied.")));
            return;
        }
        
        // Let's kill this token!
        $this->_current_token->expires = gmdate("Y-m-d H:i:s");
        $this->_current_token->save();
        
        // Now that we have the request, let's get the account!
        $account = ORM::factory("Account", $this->_current_token->account_id);
        $return = array();
        $return["cid"] = $account->id;
        $return["name_first"] = $account->name_first;
        $return["name_last"] = $account->name_last;
        $return["email"] = $account->emails->where("primary", "=", 1)->where("deleted", "IS", NULL)->find()->email;
        $return["atc_rating"] = $account->get_atc_qualification();
        $return["pilot_rating"] = $account->get_pilot_qualifications();
        $this->response->body(json_encode($return));
        return;
    }
    
    public function action_preLogin(){
        if(!$this->security()){
            $this->redirect("sso/auth/error");
        }
        
        // Since we don't want the token in the URL, let's hide it.
        Session::instance()->set("sso_token", $this->_current_token->token);
        
        // Has this member logged in before? Are we remembering them?
        if(Session::instance()->get("sso_cid", null) != null){
            $this->process_login();
            return;
        } else {
            $this->redirect("sso/auth/login");
        }
    }
        
    public function action_login(){
        // Display the login form.
        $this->setTemplate("Auth/Login");
    }
    
    protected function process_login(){
        if(!$this->security()){
            $this->redirect("sso/auth/error");
        }
        
        $security = ORM::factory("Account_Security");
        $security->type = Enum_Account_Security::HIGH;
        $security->save();
        exit();
        
        // Get the CID and Password - if no cookie is set
        if(Session::instance()->get("sso_cid", null) != null){
            $cid = Session::instance()->get("sso_cid");
        } else {
            $cid = $this->request->post("cid");
            $password = $this->request->post("password");

            // Let's perform a check against CERT
            $cert = Vatsim::factory("autotools")->authenticate($cid, $password);
            if (!$cert) {
                $this->_data["error"] = "The CID/Password combination entered was invalid.";
                $this->action_login();
                return false;
            }
        }
        
        // Get this user account.
        $this->_current_account = ORM::factory("Account", $cid);

        // Not loaded (account doesn't exist) - create a guest one for them.
        if (!$this->_current_account->loaded()) {
            // Let's get some details from CERT for them.
            $details = Vatsim::factory("autotools")->getInfo($cid);
            
            // Use the helper to create the member.
            Helper_Membership_Account::processMember(array("cid" => $cid,
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
            $details = Vatsim::factory("autotools")->getInfo($this->_current_account->id);
            if (count($details) > 0) {
                $this->_current_account->name_first = $details["name_first"];
                $this->_current_account->name_last = $details["name_last"];
                $this->_current_account->checked = gmdate("Y-m-d H:i:s");
                $this->_current_account->save();
            }
        }
        
        // Are they banned?
        if(($this->_current_account->status & bindec(Enum_Account::STATUS_SYSTEM_BANNED)) == true){
            $this->_data["error"] = "You are currently banned from the VATSIM-UK System.  Please use the link above to request support.";
            $this->action_login();
            return false;
        }

        if(($this->_current_account->status & bindec(Enum_Account::STATUS_NETWORK_BANNED)) == true){
            $this->_data["error"] = "You are currently banned from the VATSIM Network.  You cannot login until this is lifted.  If you believe this is an error, please use the link above.";
            $this->action_login();
            return false;
        }
        
        // Now that we've got this far, they're VALID! So, let's update the token.
        $this->_current_token->account_id = $cid;
        $this->_current_token->save();

        // Now store the cid in a session
        Session::instance()->set("sso_cid", $cid);
        
        // Now, where are we going?
        $this->postLoginChecks();
        
        // Or, everything is fine?
        $this->returnHome();
    }
    
    public function action_error(){
        print "ERROR!";
    }
    
    public function action_email_confirm(){
        $this->setTemplate("Manage/Email_Confirm");
    }
    
    public function process_email_confirm(){
        if(!$this->security() || !$this->_current_account->loaded()){
            $this->redirect("sso/auth/error");
            return;
        }
        
        // Is the email "valid"?
        if(!Vatsim::factory("autotools")->confirm_email($this->_current_account->id, $this->request->post("email"))){
            $this->_data["error"] = "This email address does not match your VATSIM registered one.  Please try again.";
            $this->action_email_confirm();
            return false;
        }
        
        // Store and return to the site!
        Helper_Membership_Account::processMember(array("cid" => $this->_current_account->id,
                                                       "email" => $this->request->post("email"),
                                                        "email_action" => Helper_Membership_Account::ACTION_EMAIL_CREATE_PRIMARY),
                                                 Helper_Membership_Account::ACTION_USER);
        $this->returnHome();
    }
    
    public function action_extra_security(){
        if(!$this->security() || !$this->_current_account->loaded()){
            $this->redirect("sso/auth/error");
            return;
        }
        
        $this->setTemplate("Auth/Extra_Security");
    }
    
    public function process_extra_security(){
        if(!$this->security() || !$this->_current_account->loaded()){
            $this->redirect("sso/auth/error");
            return;
        }
        
        if(sha1($this->request->post("extra_password")) != $this->_current_account->extra_password){
            $this->_data["error"] = "The second layer security you entered is invalid - please try again.";
            $this->action_extra_security();
            return false;
        }
        
        // Extra security is valid!
        $this->returnHome();
    }
    
    private function postLoginChecks(){
        // Do we need to get an email address from them?
        if($this->_current_account->emails->where("deleted", "IS", NULL)->count_all() < 1){
            $this->_current_token->expires = gmdate("Y-m-d H:i:s", strtotime("+5 minutes"));
            $this->_current_token->save();
            $this->redirect("sso/auth/email_confirm");
            return;
        }
            
        // What about an extra password?
        if($this->_current_account->extra_password != ''){
            $this->_current_token->expires = gmdate("Y-m-d H:i:s", strtotime("+5 minutes"));
            $this->_current_token->save();
            $this->redirect("sso/auth/extra_security");
            return;
        }       
    }
    
    private function returnHome(){
        Session::instance()->delete("sso_token");
        $this->redirect($this->_current_token->return_url."?_1_");
    }
    
    private function security(){
        // Does a token exist in the session?
        if(Session::instance()->get("sso_token", null) != null){
            $token = Session::instance()->get("sso_token");
            $this->_current_token = ORM::factory("Sso_Token")->where("token", "=", $token)->where("expires", ">=", gmdate("Y-m-d H:i:s"))->find();
            if(!$this->_current_token->loaded()){
                Session::instance()->delete("sso_token");
            }
        }
        
        // Since we can't find a session version, have they requested one now?
        if(Session::instance()->get("sso_token", null) == null){
            $token = $this->request->query("token");
            $ssoKey = $this->request->query("ssoKey");
            $this->_current_token = ORM::factory("Sso_Token")->where("token", "=", $token)->where("sso_key", "=", $ssoKey)->where("expires", ">=", gmdate("Y-m-d H:i:s"))->find();
        }
        
        // Do these details exist?
        if(!$this->_current_token->loaded()){
            Session::instance()->delete("sso_token");
            return false;
        }
        
        // We've got a valid token - do we need to load the account?
        if($this->_current_token->account_id > 0){
            $this->_current_account = ORM::factory("Account", $this->_current_token->account_id);
            $this->_data["_account"] = $this->_current_account;
        }
        return true;
    }
    
    private function rememberMe($account_id=null){
        // Setting?
        if($account_id != null){
            
        } else {
            
        }
    }
    
    /*
    
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
        /*$this->setTemplate("Session/Extra_Security");
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
*/
}