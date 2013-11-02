<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Account_Main extends Model_Master {

    protected $_db_group = 'mship';
    protected $_table_name = 'account';
    protected $_primary_key = 'id';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'name_first' => array('data_type' => 'string'),
        'name_last' => array('data_type' => 'string'),
        'salt' => array('data_type' => 'string'),
        'last_login' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'last_login_ip' => array('data_type' => 'int'),
        'gender' => array('data_type' => 'char', 'is_nullable' => TRUE),
        'age' => array('data_type' => 'smallint'),
        'template' => array('data_type' => 'string'),
        'created' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'updated' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'checked' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'status' => array('data_type' => 'smallint'),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );

    // Belongs to relationships
    protected $_belongs_to = array();
    
    // Has man relationships
    protected $_has_many = array(
        'notes' => array(
            'model' => 'Account_Email',
            'foreign_key' => 'account_id',
        ),
        'notes_actioner' => array(
            'model' => 'Account_Email',
            'foreign_key' => 'actioner_id',
        ),
        'emails' => array(
            'model' => 'Account_Email',
            'foreign_key' => 'account_id',
        ),
        'qualifications' => array(
            'model' => 'Account_Qualification',
            'foreign_key' => 'account_id',
        ),
        'states' => array(
            'model' => 'Account_State',
            'foreign_key' => 'account_id',
        ),
        'downloads' => array(
            'model' => 'Download',
            'foreign_key' => 'account_id',
        ),
    );
    
    // Has one relationship
    protected $_has_one = array(
        'security' => array(
            'model' => 'Account_Security',
            'foreign_key' => 'account_id',
        ),
    );
    
    // Validation rules
    public function rules(){
        return array(
            'name_first' => array(
                array('not_empty'),
            ),
            'name_last' => array(
                array('not_empty'),
            ),
            'gender' => array(
                array("regex", array(":value", "/(M|F)/i")),
            ),
        );
    }
    
    // Data filters
    public function filters(){
        return array(
            'name_first' => array(
                array('trim'),
                array(array("UTF8", "clean"), array(":value")),
                array(array("Helper_Account", "formatName"), array(":value", "f")),
            ),
            'name_last' => array(
                array('trim'),
                array(array("UTF8", "clean"), array(":value")),
                array(array("Helper_Account", "formatName"), array(":value", "s")),
            ),
            'password' => array(
                array("sha1"),
            ),
            'extra_password' => array(
                array("sha1"),
            ),
            'last_login_ip' => array(
                array("ip2long"),
            )
        );
    }
    
    /**
     * Update the last_login fields!
     * 
     * @return void
     */
    public function update_last_login_info(){
        $this->last_login = gmdate("Y-m-d H:i:s");
        $this->last_login_ip = $_SERVER["REMOTE_ADDR"];
        $this->save();
    }
    
    /**
     * Load the current authenticated user
     * 
     * @return Account_Main ORM Object.
     */
    public function get_current_account(){
        $id = $this->session()->get(ORM::factory("Setting")->getValue("auth.account.session.key"), null);
        if($id == NULL || !is_numeric($id)){
            // Get the salt value from the database.
            $cookieValue = Cookie::decrypt(ORM::factory("Setting")->getValue("auth.account.cookie.key"), null);
            if($cookieValue == NULL){
                return $this;
            }
            
            // Split the cookie into CID and Salt.
            $cookieValue = explode("|", $cookieValue);
            $id = Arr::get($cookieValue, 0, NULL);
            $salt = Arr::get($cookieValue, 1, "x");
            
            // Valid ID?
            if($id == NULL || !is_numeric($id)){
                return $this;
            }
            
            // Valid ID/Salt pair?
            $check = ORM::factory("Account_Main")->where("salt", "=", $salt)->where("id", "=", $id)->reset(FALSE)->count_all();
            if($check < 1){
                return $this;
            }
        }
        
        // Now, load THIS model properly!
        $this->__construct($id);
        return $this;
    }
        
    /**
     * Check whether this account requires an update from CERT.
     * 
     * @return boolean True if it requires an update.
     */
    public function check_requires_cert_update(){
        return ($this->loaded() && strtotime($this->checked) <= strtotime("-24 hours"));
    }
        
    /**
     * Get the last ip address used to login to this account.
     * 
     * @return string The last IP address used on this account.
     */
    public function get_last_login_ip(){
        return long2ip($this->last_login_ip);
    }
    
    /**
     * Count the number of times the specified {@link $ip} has been used to login,
     * within {@link $timeLimit}.
     * 
     * @param string $ip The IP to get the count for.  If left as NULL, the last will be used.
     * @param string $timeLimit An strtotime() string to determine the period we're checking.
     * @return int The number of times the {@link $ip} has been using within {@link $timeLimit}.
     */
    public function count_last_login_ip_usage($ip=null, $timeLimit="-8 hours"){
        // Use the last IP of this account?
        if($ip == null){
            $ip = $this->get_last_login_ip();
        }
        
        $ipCheck = ORM::factory("Account")->where("last_login_ip", "=", ip2long($ip));
        
        // Exclude this user?
        if($this->id > 0){
            $ipCheck = $ipCheck->where("id", "!=", $this->id);
        }
        
        // Limit the timeframe?
        if($timeLimit != null && $timeLimit != false){
            $ipCheck = $ipCheck->where("last_login", ">=", gmdate("Y-m-d H:i:s", strtotime($timeLimit)));
        }
        
        // Return the count.
        return $ipCheck->reset(FALSE)->count_all();
    }
    
    /** 
     * Validate the given password for this user.
     *  
     * @param string $pass The password to validate.
     * @return boolean True on success, false otherwise.
     */
    public function validate_password($pass){
        return Vatsim::factory("autotools")->authenticate($this->id, $pass);
    }
    
    /**
     * Authenticate a user using their CID and password, and then set the necessary sessions.
     * 
     * @param string $pass The password to use for authentication.
     * @return boolean True on success, false otherwise.
     */
    public function action_authenticate($pass){
        // Get the auth result - we'll let the controller catch the exception.
        $authResult = $this->validate_password($pass);

        // If we've got a valid authentication, set the session!
        if($authResult){
            $this->setSessionData(false);
            $this->update_last_login_info();
            return $authResult;
        }
        $this->session()->delete(ORM::factory("Setting")->getValue("auth.account.session.key"));
        Cookie::delete(ORM::factory("Setting")->getValue("auth.account.cookie.key"));
        $this->session()->delete("sso_quicklogin");
        
        // Default response - protects the system!
        return false;
    }
    
    /**
     * Log a user out!
     */
    public function action_logout(){
        $this->destroySessionData();
    }
    
    /**
     * If a user's details are already set, run a quick login on them!
     */
    public function action_quick_login(){
        $this->setSessionData(true);
        $this->update_last_login_info();
        return true;
    }
    
    /**
     * Update the salt for this user's account.
     */
    private function renew_salt(){
        $salt = md5(uniqid().md5(time()));
        $salt = substr($salt, 0, 20);
        $this->salt = $salt;
        $this->save();
        return $salt;
    }
    
    /**
     * Set session data!
     * 
     * @param boolean $quickLogin If TRUE, it will set a quickLogin session value.
     * @return void
     */
    private function setSessionData($quickLogin=false){
        $this->session()->set(ORM::factory("Setting")->getValue("auth.account.session.key"), $this->id);
        
        // Cookie!
        $lifetime = strtotime("+".ORM::factory("Setting")->getValue("auth.account.cookie.lifetime"));
        $lifetime = $lifetime-time();
        $salt = $this->renew_salt();
        $cookieValue = $this->id."|".$salt;
        Cookie::encrypt(ORM::factory("Setting")->getValue("auth.account.cookie.key"), $cookieValue, $lifetime);
        $this->session()->set("sso_quicklogin", $quickLogin);
    }
    
    /**
     * Destory the session data!
     * 
     * @param boolean $quickLogin If TRUE, it will set a quickLogin session value.
     * @return void
     */
    private function destroySessionData(){
        $this->session()->delete(ORM::factory("Setting")->getValue("auth.account.session.key"));
        Cookie::delete(ORM::factory("Setting")->getValue("auth.account.cookie.key"));
        $this->session()->delete("sso_quicklogin");
    }
    
    /**
     * Override the current account with another account.
     */
    public function override_enable($override_id){
        $this->session()->set("sso_account_override", $this->session()->get(ORM::factory("Setting")->getValue("auth.account.session.key")));
        $this->session()->set(ORM::factory("Setting")->getValue("auth.account.session.key"), $override_id);
    }
    
    /**
     * Override the current account with another account.
     */
    public function override_disable(){
        $this->session()->set(ORM::factory("Setting")->getValue("auth.account.session.key"), $this->session()->get("sso_account_override"));
        $this->session()->delete("sso_account_override");
    }
    
    /**
     * Check whether this account is being overriden.
     */
    public function is_overriding(){
        return !($this->session()->get("sso_account_override", null) == null);
    }
    
    /**
     * Was this login a quick login?
     * 
     * @return boolean TRUE if it was a quick login, false otherwise.
     */
    public function is_quick_login(){
        $ql = $this->session()->get("sso_quicklogin", false);
        return $ql;
    }
    
    /**
     * Determine whether the current loaded member is of the set state.
     * 
     * @param Enum_Account_State $state The state to check.
     * @param string $returnType boolean or date.
     * @return boolean True if set, false otherwise.
     */
    public function isStateSet($state, $returnType="boolean"){
        foreach($this->states->find_all() as $_s){
            if(is_object($_s) && $_s->state == $state && $_s->removed == NULL){
                return (($returnType == "date") ? $_s->created : true);
            }
        }
        return false;
    }
    
    /**
     * Get all the state flags for this user.
     * 
     * @return array An array of states -> boolean key/value pair.
     */
    public function getStates(){
        $return = array();
        foreach(Enum_Account_State::getAll() as $key => $value){
            $return[strtolower($key)] = (int) $this->isStateSet($value);
            if($return[strtolower($key)])
                $return[strtolower($key)."_date"] = $this->isStateSet($value, "date");
        }
        return $return;
    }
    
    /**
     * Get all current status flags for a user's account.
     * 
     * @return array An array of status => boolean pairs.
     */
    public function getStatusFlags(){
        // Now, sort out the status!
        $return = array();
        foreach(Enum_Account_Status::getAll() as $key => $value){
            $return[strtolower($key)] = (int) $this->isStatusFieldSet($value);
        }
        return $return;
    }
    
    /**
     * Determine whether a status is set on the user's account.
     * 
     * @param Enum_Account_Status $status The status to check.
     * @return boolean True if set, false otherwise.
     */
    public function isStatusFieldSet($status){
        return (boolean) (bindec($status) & $this->status);
    }
    
    /**
     * Determine whether a user is banned in anyway!
     * 
     * @return boolean True if banned, false otherwise.
     */
    public function isBanned(){
        return $this->isStatusFieldSet(Enum_Account_Status::SYSTEM_BANNED) OR $this->isStatusFieldSet(Enum_Account_Status::NETWORK_BANNED);
    }
}

?>