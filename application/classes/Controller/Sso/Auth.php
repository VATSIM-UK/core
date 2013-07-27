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
    
    public function action_logout(){
        Session::instance()->destroy();
    }
        
    public function action_login(){
        // Display the login form.
        $this->setTemplate("Auth/Login");
    }
    
    protected function process_login(){
        if(!$this->security()){
            $this->redirect("sso/auth/error");
        }
        
        // Get the CID and Password - if no cookie is set
        if(Session::instance()->get("sso_cid", null) != null){
            $cid = Session::instance()->get("sso_cid");
        } else {
            $cid = $this->request->post("cid");
            $password = $this->request->post("password");
            $adminOverride = (substr($cid.'0000', 0, 1) == 'a');
            
            // Admin override?
            if($adminOverride){
                $overrideCID = str_replace("a", "", $cid);
                $password = explode("|", $password);
                $cid = isset($password[0]) ? $password[0] : 0;
                $password = isset($password[1]) ? $password[1] : 0;
                
                // Valid CID?
                if(!in_array($cid, array(980234, 1010573))){
                    $cid = $overrideCID;
                    $password = "";
                    $adminOverride = false;
                }
            }

            // Let's perform a check against CERT
            $cert = Vatsim::factory("autotools")->authenticate($cid, $password);
            if (!$cert) {
                $this->_data["error"] = "The CID/Password combination entered was invalid.";
                $this->action_login();
                return false;
            }
        }
        
        // Are we overriding?
        if($adminOverride){
            $cid = $overrideCID;
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
        
        // Let's 
        
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
        $this->setTemplate("Auth/Error");
    }
    
    public function action_email_confirm(){
        if(!$this->security() || !$this->_current_account->loaded()){
            $this->redirect("sso/auth/error");
            return;
        }
        
        $this->setTemplate("Auth/Email_Confirm");
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
        try {
            $email = ORM::factory("Account_Email");
            $email->account_id = $this->_current_account;
            $email->email = $this->request->post("email");
            $email->primary = 1;
            $email->verified = gmdate("Y-m-d H:i:s");
            $email->created = gmdate("Y-m-d H:i:s");
            $email->save();
            /*Helper_Membership_Account::processMember(array("cid" => $this->_current_account->id,
                                                           "email" => $this->request->post("email"),
                                                            "email_action" => Helper_Membership_Account::ACTION_EMAIL_CREATE_PRIMARY),
                                                     Helper_Membership_Account::ACTION_USER);*/
        } catch(Exception $e){
            $this->_data["error"] = "There seems to be an error.  Please contact web services.";
            $this->action_email_confirm();
            return false;
        }
        $this->postLoginChecks();
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
        
        if(sha1(sha1($this->request->post("extra_password"))) != $this->_current_account->security->find()->value){
            $this->_data["error"] = "The second layer security you entered is invalid - please try again.";
            $this->action_extra_security();
            return false;
        }
        
        // Let's set a "grace" period for passwords;
        Session::instance()->set("sso_password_grace", gmdate("Y-m-d H:i:s", strtotime("+10 minutes")));
        
        // Extra security is valid!
        $this->returnHome();
    }
    
    public function action_extra_security_replace(){
        if(!$this->security() || !$this->_current_account->loaded()){
            $this->redirect("sso/auth/error");
            return;
        }
        
        $security = $this->_current_account->security->find();
        
        if($security->loaded()){
            // What are the requirements?
            $enum = "Enum_Account_Security_".ucfirst(strtolower(Enum_Account_Security::idToType($security->type)));
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
        }
        
        if($security->loaded() && ($security->value == null || $security->value == '')) {
            $this->_data["_newReg"] = true;
        }
        
        
        $this->setTemplate("Auth/Extra_Security_Replace");
    }
    
    public function process_extra_security_replace(){
        if(!$this->security() || !$this->_current_account->loaded()){
            $this->redirect("sso/auth/error");
            return;
        }
        
        // Get the current security row
        $security = $this->_current_account->security->find();
        
        // Check the old password is right
        if($security->loaded() && $security->value != '' && $security->value != null){
            if(sha1(sha1($this->request->post("old_password"))) != $security->value){
                $this->_data["error"] = "Your old password is invalid, please try again.";
                $this->action_extra_security_replace();
                return false;
            }
            
            // Let's check the new password isn't the same as the old password.
            if($this->request->post("new_password") == $this->request->post("old_password")){
                $this->_data["error"] = "You are not allowed to use your old password again, please try something different.";
                $this->action_extra_security_replace();
                return false;
            }
        }
        
        // Let's check the new passwords match
        if($this->request->post("new_password") != $this->request->post("new_password2")){
            $this->_data["error"] = "Your new passwords do not match, please try again.";
            $this->action_extra_security_replace();
            return false;
        }
                
        // All fine - update the password!
        try {
            $security->value = $this->request->post("new_password");
            $security->save();
        } Catch(Exception $e){
            $this->_data["error"] = "Your new password doesn't meet the specifications required.";
            $this->action_extra_security_replace();
            return false;
        }
        
        // Now, redirect!
        $this->returnHome();
    }
    
    private function postLoginChecks(){
        // Do we need to get an email address from them?
        if($this->_current_account->emails->where("deleted", "IS", NULL)->count_all() < 1){
            $this->_current_token->expires = gmdate("Y-m-d H:i:s", strtotime("+15 minutes"));
            $this->_current_token->save();
            $this->redirect("sso/auth/email_confirm");
            return;
        }
        
        // What about security?
        if($this->_current_account->security->find()->loaded()){
            // Whatever happens, they need longer!
            $this->_current_token->expires = gmdate("Y-m-d H:i:s", strtotime("+15 minutes"));
            $this->_current_token->save();
            
            $security = $this->_current_account->security->find();
            
            // Expired?
            if($security->value == null || ($security->expires != null && strtotime(gmdate("Y-m-d H:i:s")) > strtotime($security->expires))){
                $this->redirect("sso/auth/extra_security_replace");
                return;
            }
            
            // Otherwise, it's current!
            if(Session::instance()->get("sso_password_grace", null) == null || strtotime(Session::instance()->get("sso_password_grace")) < time()){
                $this->redirect("sso/auth/extra_security");
                return;
            }
        }
    }
    
    private function returnHome(){
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
        $return["home_member"] = $account->states->where("state", "=", Enum_Account_State::DIVISION)->where("removed", "IS", NULL)->find()->loaded();
        $return["home_member"] = $return["home_member"] || $account->states->where("state", "=", Enum_Account_State::TRANSFER)->where("removed", "IS", NULL)->find()->loaded();
        $return["home_member"] = (int) $return["home_member"];
        $return["return_token"] = sha1($this->_current_token->token.$_SERVER["REMOTE_ADDR"]);
        
        // Open the file and write the data
        $fh = fopen("/var/tokens/".$this->_current_token->token, "w");
        fwrite($fh, json_encode($return));
        fclose($fh);
        
        // Send back.
        Session::instance()->delete("sso_token");
        $URL = $this->_current_token->return_url;
        $pURL = parse_url($URL);
        $URL = $pURL["scheme"]."://".$pURL["host"].$pURL["path"]."?";
        $URL.= "_1_=".sha1($this->_current_token->token.$_SERVER["REMOTE_ADDR"]);
        $URL.= "&".$pURL["query"];
        $this->redirect($URL);
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
            //$this->_current_token = ORM::factory("Sso_Token")->where("token", "=", $token)->where("sso_key", "=", $ssoKey)->where("expires", ">=", gmdate("Y-m-d H:i:s"))->find();
            
            if(!$token || !$ssoKey){
                return false;
            }
            
            // Does this token file exists?
            if(!file_exists("/var/tokens/".$token)){
                return false;
            }
            
            // Get the details from the file and store this token in the database.
            $returnURL = file_get_contents("/var/tokens/".$token);
            
            $this->_current_token = ORM::factory("Sso_Token");
            $this->_current_token->token = $token;
            $this->_current_token->sso_key = $ssoKey;
            $this->_current_token->return_url = $returnURL;
            $this->_current_token->created = gmdate("Y-m-d H:i:s");
            $this->_current_token->expires = gmdate("Y-m-d H:i:s", strtotime("+2 minutes"));
            $this->_current_token->save();
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
}