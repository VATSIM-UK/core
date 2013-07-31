<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Sso_Auth extends Controller_Sso_Master {

    protected $_permissions = array(
        "_" => array('*'),
    );
    
    private $_current_token = null;
    private $_current_account = null;
    private $_actual_account = null;

    public function getDefaultAction() {
        return "login";
    }

    public function before() {
        parent::before();
    }

    public function after() {
        parent::after();
    }
    
    public function action_display(){
        // If they're not logged in, we'll go to the preLogin page.
        if(Session::instance()->get("sso_cid", null) == null){
            $this->redirect("sso/auth/error");
            return;
        }
        
        // Get the account details
        $account = ORM::factory("Account", Session::instance()->get("sso_cid"));
        
        // Any actions?
        /*if($this->request->query("security", null) == 1){
            // SET SECOND LAYER!
            if(!$account->security->loaded()){
                $security = ORM::factory("Account_Security");
                $security->account_id = $account;
                $security->save();
                $account->reload();
            }
        } else if($this->request->query("security", null) == 0){
            // DISABLE SECOND LAYER!
            if($account->security->loaded()){
                $account->security->delete();
                $account->reload();
            }
        }*/
        
        // If they're not loaded, error
        if(!$account->loaded()){
            $this->redirect("sso/auth/error");
            return;
        }
        
        // Set the account details
        $this->_data["_account"] = $account;
        
        // Display the holding page, for somebody that's logged in.
        $this->setTemplate("Auth/Display");
    }
    
    public function action_override(){
        // If they're not logged in, we'll go to the error page.
        if(Session::instance()->get("sso_cid", null) == null){
            $this->redirect("sso/auth/error");
            return;
        }
        
        // KH or AL?
        if(!in_array(Session::instance()->get("sso_cid"), array(980234, 1010573))){
            $this->redirect("sso/auth/error");
            return;
        }
        
        // Get the account details
        $account = ORM::factory("Account", Session::instance()->get("sso_cid"));
        
        // If they're not loaded, error
        if(!$account->loaded()){
            $this->redirect("sso/auth/error");
            return;
        }
        
        // Set the account details
        $this->_data["_account"] = $account;
        
        // Display the holding page, for somebody that's logged in.
        $this->setTemplate("Auth/Override");
    }
    
    public function process_override(){
        // If they're not logged in, we'll go to the error page.
        if(Session::instance()->get("sso_cid", null) == null){
            $this->redirect("sso/auth/error");
            return;
        }
        
        // KH or AL?
        if(!in_array(Session::instance()->get("sso_cid"), array(980234, 1010573))){
            $this->redirect("sso/auth/error");
            return;
        }
        
        // Get the account details
        $account = ORM::factory("Account", Session::instance()->get("sso_cid"));
        
        // If they're not loaded, error
        if(!$account->loaded()){
            $this->redirect("sso/auth/error");
            return;
        }
        
        // Validate the override details.
        if($account->security->value != sha1(sha1($this->request->post("extra_password")))){
            $this->_data["error"] = "Invalid second layer security.";
            $this->action_override();
            return false;
        }
        
        // Check override
        $ovrAccount = ORM::factory("Account", $this->request->post("override_cid"));
        if(!$ovrAccount->loaded()){
            $this->_data["error"] = "Invalid override CID.";
            $this->action_override();
            return false;
        }
        
        // Override!
        Session::instance()->set("sso_override", Session::instance()->get("sso_cid"));
        Session::instance()->set("sso_cid", $ovrAccount->id);
        
        // Send to display.
        $this->redirect("sso/auth/display");
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
            Session::instance()->set("sso_fast_login", true);
            $this->process_login();
            return;
        } else {
            $this->redirect("sso/auth/login");
        }
    }
    
    public function action_logout(){
        if($this->request->query("returnURL") == null || $this->request->query("ssoKey") == null){
            $this->redirect("sso/auth/error");
        }
        
        Session::instance()->set("logout_url", $this->request->query("returnURL"));
        
        // Add the key to the form.
        $this->_data["area"] = $this->request->query("ssoKey");
        
        // Display the login form.
        $this->setTemplate("Auth/Logout");
    }
    
    public function process_logout(){
        $returnURL = Session::instance()->get("logout_url");
        
        // Result?
        if($this->request->post("processlogout") == 1){
            Session::instance()->destroy();
        }
        
        $this->redirect($returnURL);
        return;
    }
        
    public function action_login(){
        // Display the login form.
        $this->setTemplate("Auth/Login");
    }
    
    protected function process_login(){
        if(!$this->security()){
            $this->redirect("sso/auth/error");
        }
        
        // Session and fast login?
        if(Session::instance()->get("sso_cid", null) != null && Session::instance()->get("sso_fast_login", null) != null){
            $cid = Session::instance()->get("sso_cid");
        } else {
            $cid = $this->request->post("cid");
            $password = $this->request->post("password");
            
            // Let's perform a check against CERT
            try {
                $cert = Vatsim::factory("autotools")->authenticate($cid, $password);
                if (!$cert) {
                    $this->_data["error"] = "The CID/Password combination entered was invalid.";
                    $this->action_login();
                    return false;
                }
            } catch(Exception $e){
                // Since we're unable to login to CERT, can we validate against their second layer security?
                $this->_current_account = ORM::factory("Account", $cid);
                $security = $this->_current_account->security->find();
                if(!$security->loaded() || sha1(sha1($password)) != $security->value){
                    $this->_data["error"] = "The VATSIM Certificate Server is currently unavailable and we cannot validate your details, please try again later.";
                    $this->_data["error"].= "Alternatively, if you have a second layer security password set, you can enter it now.";
                    $this->action_login();
                    return false;
                }
                
                // Let's set a session to say we've logged in using SLS and we don't need to be asked for it AGAIN, unless expired.
                Session::instance()->set("sso_login_sls", true);
                Session::instance()->set("sso_password_grace", gmdate("Y-m-d H:i:s", strtotime("+2 hours")));
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
            
            $this->_current_account = ORM::factory("Account", $cid);
            
            if(!$this->_current_account->loaded()){
                return false;
            }
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
        
        // Let's update their last login information.
        $this->_current_account->last_login = gmdate("Y-m-d H:i:s");
        $this->_current_account->last_login_ip = $_SERVER["REMOTE_ADDR"];
        $this->_current_account->save();
        
        // Now update the account ID in the token.
        $this->_current_token->account_id = $this->_current_account;
        $this->_current_token->save();
        
        // Now store the cid in a session
        Session::instance()->set("sso_cid", $cid);
        
        // Now, where are we going?
        $this->postLoginChecks();
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
        try {
            if(!Vatsim::factory("autotools")->confirm_email($this->_current_account->id, $this->request->post("email"))){
                $this->_data["error"] = "This email address does not match your VATSIM registered one.  Please try again.";
                $this->action_email_confirm();
                return false;
            }
        } catch(Exception $e){
            $this->_data["error"] = "The VATSIM Certificate Server is currently offline and we are unable to validate your request.";
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
        
        if(sha1(sha1($this->request->post("extra_password"))) != $this->_current_account->security->value){
            $this->_data["error"] = "The second layer security you entered is invalid - please try again.";
            $this->action_extra_security();
            return false;
        }
        
        // Let's set a "grace" period for passwords;
        Session::instance()->set("sso_password_grace", gmdate("Y-m-d H:i:s", strtotime("+2 hours")));
        
        // Extra security is valid!
        $this->postLoginChecks();
    }
    
    public function action_extra_security_replace(){
        if(!$this->security() || !$this->_current_account->loaded()){
            $this->redirect("sso/auth/error");
            return;
        }
        
        $security = $this->_current_account->security;
        
        if($security->loaded()){
            // What are the requirements?
            $enum = "Enum_Account_Security_".ucfirst(strtolower(Enum_Account_Security::valueToType($security->type)));
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
        $security = $this->_current_account->security;
        
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
            $security->created = null;
            $security->expires = null;
            $security->save();
        } Catch(Exception $e){
            $this->_data["error"] = "Your new password doesn't meet the specifications required.";
            $this->action_extra_security_replace();
            return false;
        }
        
        // Add a grace period for the second layer password.
        Session::instance()->set("sso_password_grace", gmdate("Y-m-d H:i:s", strtotime("+2 hours")));
        
        // Now, redirect!
        $this->postLoginChecks();
    }
    
    public function action_checkpoint(){
        if(!$this->security()){
            $this->redirect("sso/auth/error");
            return;
        }
        
        // Let's see what checkpoint we need - member or staff
        if($this->_current_account->security->loaded()){
            $this->_data["checkpoint_type"] = "staff";
        } else {
            $this->_data["checkpoint_type"] = "member";
        }
        
        $this->setTemplate("Auth/Checkpoint");
    }
    
    public function process_checkpoint(){
        if(!$this->security() || !$this->_current_account->loaded()){
            $this->redirect("sso/auth/error");
            return;
        }
        
        // Which checkpoint type?
        if($this->_current_account->security->loaded() || $this->_current_account->security->find()->loaded()){ // Staff
            if(sha1(sha1($this->request->post("extra_password"))) != $this->_current_account->security->value){
                $this->_data["error"] = "The second layer security you entered is invalid - please try again.";
                $this->action_extra_security();
                return false;
            }
        } else {
            try {
                if(!Vatsim::factory("autotools")->authenticate(Session::instance()->get("sso_cid"), $this->request->post("password"))){
                    $this->_data["error"] = "Your VATSIM password has not been recognised, please try again.";
                    $this->action_extra_security();
                    return false;
                }
            } catch(Exception $e){
                    $this->_data["error"] = "The VATSIM Certificate Server is currently unavailable and we cannot validate your details.  Please try again later.";
                    $this->action_extra_security();
                    return false;
            }
        }
        
        // Let's set a "grace" period for passwords;
        Session::instance()->set("sso_password_grace", gmdate("Y-m-d H:i:s", strtotime("+2 hours")));
        
        // Extra security is valid!
        Session::instance()->set("sso_checkpoint", true);
        $this->postLoginChecks();
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
        if($this->_current_account->security->loaded() && $this->_current_account->security == $this->_current_account){
            // Whatever happens, they need longer!
            $this->_current_token->expires = gmdate("Y-m-d H:i:s", strtotime("+15 minutes"));
            $this->_current_token->save();
            
            $security = $this->_current_account->security;
            
            // Expired?
            if($security->value == null || ($security->expires != null && strtotime(gmdate("Y-m-d H:i:s")) > strtotime($security->expires))){
                $this->redirect("sso/auth/extra_security_replace");
                return;
            }
            
            // Otherwise, it's current - if they haven't already entered it!
            if(Session::instance()->get("sso_login_sls", null) == null){
                if(Session::instance()->get("sso_password_grace", null) == null || strtotime(Session::instance()->get("sso_password_grace")) < time()){
                    $this->redirect("sso/auth/extra_security");
                    return;
                }
            }
        }
        
        // They've logged in before, but let's just check nobody else is using the same IP!!
        if(Session::instance()->get("sso_fast_login", null) != null && Session::instance()->get("sso_checkpoint", null) == null){
            $ipCheckCount = $this->_current_account->count_last_login_ip_usage($_SERVER["REMOTE_ADDR"]);
            if($ipCheckCount > 0){
                $this->action_checkpoint();
                return;
            }
        }
        Session::instance()->delete("sso_fast_login");
        Session::instance()->delete("sso_checkpoint");
        Session::instance()->delete("sso_override");
        
        $this->returnHome();
    }
    
    private function returnHome(){
        // Let's kill this token!
        $this->_current_token->expires = gmdate("Y-m-d H:i:s");
        $this->_current_token->save();
        
        // Now that we have the request, let's get the account!
        $account = $this->_current_account;
        $return = array();
        $return["cid"] = $account->id;
        $return["name_first"] = $account->name_first;
        $return["name_last"] = $account->name_last;
        $return["email"] = $account->emails->where("primary", "=", 1)->where("deleted", "IS", NULL)->find()->email;
        $return["atc_rating"] = ($account->qualifications->get_current_atc() ? $account->qualifications->get_current_atc()->value : Enum_Account_Qualification_ATC::UNKNOWN);
        $return["pilot_rating"] = array();
        foreach($account->qualifications->get_all_pilot() as $qual){
            $return["pilot_rating"][] = $qual->value;
        }
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
        Session::instance()->delete("sso_fast_login");
        Session::instance()->delete("sso_login_sls");
        
        // Return URL
        $URL = $this->_current_token->return_url;
        $pURL = parse_url($URL);
        $URL = $pURL["scheme"]."://".$pURL["host"].$pURL["path"]."?";
        $URL.= "_1_=".sha1($this->_current_token->token.$_SERVER["REMOTE_ADDR"]);
        $URL.= "&".Arr::get($pURL, "query", "");
        $this->redirect($URL);
    }
    
    private function security(){
        // Are we overriding?
        if(Session::instance()->get("sso_override", null) != null){
            $this->_actual_account = ORM::factory("Account", Session::instance()->get("sso_override"));
        }
        
        // Does a token exist in the session?
        if(Session::instance()->get("sso_token", null) != null){
            $token = Session::instance()->get("sso_token");
            $this->_current_token = ORM::factory("Sso_Token")->where("token", "=", $token)->where("expires", ">=", gmdate("Y-m-d H:i:s"))->find();
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
        } else {
            $this->_current_account = ORM::factory("Account");
        }
        return true;
    }
}