<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Sso_Token extends Model_Master {

    protected $_db_group = 'sso';
    protected $_table_name = 'token';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'token' => array('data_type' => 'varchar'),
        'sso_key' => array('data_type' => 'varchar'),
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
            'model' => 'Account',
            'foreign_key' => 'account_id',
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
    
    /**
     * Check the token file exists for this token.
     * 
     * @param string $token The token to check against (optional).
     * @return boolean TRUE if exists, FALSE otherwise.
     */
    public function check_token_file($token=null){
        if($token == null){
            $this->get_current_token();
            $token = $this->token;
        }
        return file_exists("/var/tokens/".$token);
    }
    
    /**
     * Get the contents of the token file.
     * 
     * @param string $token The token to get the contents of (optional)
     * @return string The contents of the token file.
     */
    public function get_token_file($token=null){
        if($token == null){
            $this->get_current_token();
            $token = $this->token;
        }
        return file_get_contents("/var/tokens/".$token);
    }
    
    /**
     * Generate the return data for a token file.
     * 
     * This will also write to the token file!
     * 
     * @param string $token The token to generate the return data for (optional).
     * @return void
     */
    public function generate_token_file_return_data($token=null){
        $this->get_current_token($token);
        $this->expire_current_token($token);
        
        // Return data!
        $account = ORM::factory("Account_Main", $this->account_id);
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
        
        // Save the return data to the token file.
        $fh = fopen("/var/tokens/".$this->token, "w");
        fwrite($fh, json_encode($return));
        fclose($fh);
        
        // Delete the session token
        Session::instance(ORM::factory("Setting")->getValue("system.session.type"))->delete(ORM::factory("Setting")->getValue("sso.token.key"));
    }
    
    /**
     * Load the current or requested token.
     * 
     * @param string $token If set, this token will be loaded.
     * @return Sso_Token ORM Object.
     */
    public function get_current_token($token=null){
        if($token == null){
            $token = Session::instance(ORM::factory("Setting")->getValue("system.session.type"))->get(ORM::factory("Setting")->getValue("sso.token.key"), null);
            if($token == NULL){
                return $this;
            }
        }
        
        // Load it!
        $token = ORM::factory("Sso_Token")
                    ->where("token", "=", $token)
                    ->where("expires", ">", gmdate("Y-m-d H:i:s"))
                    ->limit(1)
                    ->find();
        
        // Now, load THIS model properly!
        $this->__construct($token->id);
        return $this;
    }
    
    /**
     * Set the account idea for the current request.
     * 
     * @param int $cid The CID to set!
     * @return void
     */
    public function set_account_id($cid){
        $this->_account_id;
        $this->save();
    }
    
    /**
     * Start a new token for the current request.
     * 
     * This will also kill all old tokens if the session is currently set.
     * 
     * @param string $token The token to start.
     * @param string $key The section/area/module key.
     * @param string $returnURL The URL to return the member to.
     * @param boolean True on success, false otherwise.
     */
    public function set_current_token($token, $key, $returnURL=null){
        // First, let's kill the old token if it's currently set.
        $oldToken = $this->get_current_token();
        if($oldToken->loaded()){
            $oldToken->expires = gmdate("Y-m-d H:i:s", strtotime("-30 seconds"));
            $oldToken->save();
        }
        Session::instance(ORM::factory("Setting")->getValue("system.session.type"))->delete(ORM::factory("Setting")->getValue("sso.token.key"));
        
        // Now, start a new one!
        $newToken = ORM::factory("Sso_Token");
        $newToken->token = $token;
        $newToken->sso_key = $key;
        $newToken->return_url = $returnURL;
        $newToken->created = gmdate("Y-m-d H:i:s");
        $newToken->expires = gmdate("Y-m-d H:i:s", strtotime("+".ORM::factory("Setting")->getValue("sso.token.expires")));
        $newToken->save();
        
        // Store this token!
        Session::instance(ORM::factory("Setting")->getValue("system.session.type"))->set(ORM::factory("Setting")->getValue("sso.token.key"), $token);
        
        return $newToken;
    }
    
    /**
     * For a token to expire.
     * 
     * @return void
     */
    public function expire_current_token($token=null){
        $this->get_current_token($token);
        $this->expires = gmdate("Y-m-d H:i:s");
        $this->save();
    }
}

?>