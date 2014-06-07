<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Sso_Token extends Model_Master {

    protected $_db_group = 'sso';
    protected $_table_name = 'token';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'token' => array('data_type' => 'varchar'),
        'sso_account_id' => array('data_type' => 'smallint'),
        'return_url' => array('data_type' => 'varchar'),
        'account_id' => array('data_type' => 'int'),
        'created' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'expires' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
        'account' => array(
            'model' => 'Sso_Account',
            'foreign_key' => 'sso_account_id',
        ),
    );
    
    // Has man relationships
    protected $_has_many = array();
    
    // Has one relationship
    protected $_has_one = array();
    
    // Validation rules
    public function rules(){
        return array();
    }
    
    // Data filters
    public function filters(){
        return array();
    }
    
    public function locate($token){
        return $this->where("token", "=", $token)->find();
    }
    
    public function isExpired(){
        if($this->loaded()){
            if(time() - strtotime($this->expires." GMT") > 0){
                return true;
            } else {
                return false;
            }
        }
        return true;
    }
    
    public function generate_token_file_return_data($token=null){
        $this->get_current_token($token);
        $this->expire_current_token($token);
        
        // Return data!
        $account = ORM::factory("Account_Main", $this->account_id);
        $return = array();
        $return["cid"] = $account->id;
        $return["name_first"] = $account->name_first;
        $return["name_last"] = $account->name_last;
        $return["name_full"] = $return["name_first"]." ".$return["name_last"];
        $return["email"] = $account->emails->assigned_to_sso($this->sso_key, $account->id, true);
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
        
        // OLD-Remove BB-Core-66
        $return["pilot_rating"] = $return["pilot_ratings"];
        
        $return["home_member"] = $account->states->where("state", "=", Enum_Account_State::DIVISION)->where("removed", "IS", NULL)->find()->loaded();
        $return["home_member"] = $return["home_member"] || $account->states->where("state", "=", Enum_Account_State::TRANSFER)->where("removed", "IS", NULL)->find()->loaded();
        $return["home_member"] = (int) $return["home_member"];
        
        $return["fields_pending_deletion"] = array("pilot_rating", "home_member");
        //-
        
        $return["account_state"] = $account->getStates();
        $return["account_status"] = $account->getStatusFlags();
        $return["return_token"] = sha1($this->token.$_SERVER["REMOTE_ADDR"]);
        
        // Save the return data to the token file.
        $fh = fopen("/var/tokens/".$this->token, "w");
        fwrite($fh, json_encode($return));
        fclose($fh);
        
        // Delete the session token
        $this->session()->delete(ORM::factory("Setting")->getValue("auth.sso.token.key"));
    }
}

?>