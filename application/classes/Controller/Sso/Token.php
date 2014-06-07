<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Sso_Token extends Controller_Sso_Master {
    private $_ssoAccount = null;
    
    private function security(){
        // We should've been passed the username and public API Key here.
        if(!$this->request->post("username")){
            $this->respondJson(array("status" => "error", "error" => "NO_USERNAME"));
            return;
        }
        
        if(!$this->request->post("apikey_pub")){
            $this->respondJson(array("status" => "error", "error" => "NO_APIKEY_PUB"));
            return;
        }
        
        // Authenticate...
        $this->_ssoAccount = ORM::factory("Sso_Account")->where("username", "=", $this->request->post("username"))->find();
        if(!$this->_ssoAccount->loaded() OR $this->_ssoAccount->api_key_public != $this->request->post("apikey_pub")){
            $this->respondJson(array("status" => "error", "error" => "INVALID_CREDENTIALS"));
            return;
        }
    }
    
    public function action_generate(){
        $this->security();
        
        // We should also have provided a return URL so we've got this stored.
        if(!$this->request->post("return_url")){
            $this->respondJson(array("status" => "error", "error" => "NO_RETURN_URL"));
            return;
        }
        
        // Create a token...
        $token = ORM::factory("Sso_Token");
        $_t = sha1(uniqid($this->_ssoAccount->username, true));
        $token->token = md5($_t . $this->_ssoAccount->api_key_private);
        $token->sso_account_id = $this->_ssoAccount->id;
        $token->return_url = $this->request->post("return_url");
        $token->created = gmdate("Y-m-d H:i:s");
        $token->expires = gmdate("Y-m-d H:i:s", strtotime("+10 seconds"));
        $token->save();
        
        // We want to return the token to the user for later use in their requests.
        $this->respondJson(array("status" => "success", "token" => $_t, "timestamp" => strtotime($token->created)));
        return;
    }
    public function action_details(){
        $this->security();
        
        // We should also have an access token.
        if(!$this->request->post("access_token")){
            $this->respondJson(array("status" => "error", "error" => "NO_ACCESS_TOKEN"));
            return;
        }
        
        // Check the access token...
        $accessToken = ORM::factory("Sso_Token")->locate($this->request->post("access_token"));
        if(!$accessToken->loaded() OR $accessToken->sso_account_id != $this->_ssoAccount->id OR $accessToken->isExpired()){
            $this->respondJson(array("status" => "error", "error" => "INV_ACCESS_TOKEN"));
            return;
        }
        
        $accessToken->expires = gmdate("Y-m-d H:i:s");
        $accessToken->save();
        
        // Create the response...
        $account = ORM::factory("Account_Main", $accessToken->account_id);
        $return = array();
        $return["cid"] = $account->id;
        $return["name_first"] = $account->name_first;
        $return["name_last"] = $account->name_last;
        $return["name_full"] = $return["name_first"]." ".$return["name_last"];
        $return["email"] = $account->emails->assigned_to_sso($this->_ssoAccount->id, $account->id, true);
        $return["atc_rating"] = ($account->qualifications->get_current_atc() ? $account->qualifications->get_current_atc()->value : Enum_Account_Qualification_ATC::UNKNOWN);
        $return["atc_rating_human_short"] = Enum_Account_Qualification_ATC::valueToType($return["atc_rating"]);
        $return["atc_rating_human_long"] = Enum_Account_Qualification_ATC::getDescription($return["atc_rating"]);
        $return["atc_rating_date"] = $account->qualifications->get_current_atc()->created;
        
        $return["pilot_ratings"] = array();
        if(count($account->qualifications->get_all_pilot()) < 1){
            $return["pilot_ratings"][] = 0;
            $return["pilot_ratings_human_short"][] = "NA";
            $return["pilot_ratings_human_long"][] = "None Awarded";
        } else {
            foreach($account->qualifications->get_all_pilot() as $qual){
                $e = array();
                $e["rating"] = $qual->value;
                $e["human_short"] = Enum_Account_Qualification_Pilot::valueToType($qual->value);
                $e["human_long"] = Enum_Account_Qualification_Pilot::getDescription($qual->value);
                $e["date"] = $qual->created;
                $return["pilot_ratings"][] = (array) $e;
            }
        }
        
        $return["admin_ratings"] = array();
        foreach($account->qualifications->get_all_admin() as $qual){
            $e = array();
            $e["rating"] = $qual->value;
            $e["human_short"] = Enum_Account_Qualification_Admin::valueToType($qual->value);
            $e["human_long"] = Enum_Account_Qualification_Admin::getDescription($qual->value);
            $e["date"] = $qual->created;
            $return["admin_ratings"][] = (array) $e;
        }
        
        $return["training_pilot_ratings"] = array();
        foreach($account->qualifications->get_all_training("pilot") as $qual){
            $e = array();
            $e["rating"] = $qual->value;
            $e["human_short"] = Enum_Account_Qualification_Training_Pilot::valueToType($qual->value);
            $e["human_long"] = Enum_Account_Qualification_Training_Pilot::getDescription($qual->value);
            $e["date"] = $qual->created;
            $return["training_pilot_ratings"][] = (array) $e;
        }
        
        $return["training_atc_ratings"] = array();
        foreach($account->qualifications->get_all_training("atc") as $qual){
            $e = array();
            $e["rating"] = $qual->value;
            $e["human_short"] = Enum_Account_Qualification_Training_ATC::valueToType($qual->value);
            $e["human_long"] = Enum_Account_Qualification_Training_ATC::getDescription($qual->value);
            $e["date"] = $qual->created;
            $return["training_atc_ratings"][] = (array) $e;
        }
        
        $return["account_state"] = $account->getStates();
        $return["account_status"] = $account->getStatusFlags();
        
        // We want to return the token to the user for later use in their requests.
        $this->respondJson(array("status" => "success", "data" => $return));
        return;
    }
}