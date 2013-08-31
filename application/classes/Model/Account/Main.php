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
        'password' => array('data_type' => 'string'),
        'extra_password' => array('data_type' => 'string'),
        'last_login' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'last_login_ip' => array('data_type' => 'int'),
        'gender' => array('data_type' => 'char', 'is_nullable' => TRUE),
        'age' => array('data_type' => 'smallint'),
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
                array(array($this, "formatName"), array(":value", "f")),
            ),
            'name_last' => array(
                array('trim'),
                array(array("UTF8", "clean"), array(":value")),
                array(array($this, "formatName"), array(":value", "s")),
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
        $id = $this->_session->get(ORM::factory("Setting")->getValue("session.account.key"), null);
        if($id == NULL || !is_numeric($id)){
            return $this;
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
        $authResult = $this->validate_password();

        // If we've got a valid authentication, set the session!
        if($authResult){
            $this->setSessionData(false);
            $this->update_last_login_info();
            return $authResult;
        }
        
        // Default response - protects the system!
        return false;
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
     * Set session data!
     * 
     * @param boolean $quickLogin If TRUE, it will set a quickLogin session value.
     * @return void
     */
    private function setSessionData($quickLogin=false){
        $this->_session->set(ORM::factory("Setting")->getValue("session.account.key"), $this->id);
        $this->_session->set(ORM::factory("Setting")->getValue("sso.quicklogin.key"), $quickLogin);
    }
    
    /**
     * Was this login a quick login?
     * 
     * @return boolean TRUE if it was a quick login, false otherwise.
     */
    public function is_quick_login(){
        $ql = $this->_session->get(ORM::factory("Setting")->getValue("sso.quicklogin.key", false));
        return $ql;
    }
    
    /**
     * This helper formats the name of a person to conform with expected output.
     * 
     * @param string $name The name to format.
     * @param string $type 'f' for forename, 's' for surname.
     * @return string The formatted name.
     */
    public static function formatName($name, $type='f'){
        //Firstname
        if($type == 'f'){

            $name = trim($name);
            $name = ucfirst(strtolower($name));
            $name = addslashes($name);

            return $name;

        ///Surname
        } elseif($type == 's') {

            $name = trim($name);

            ///Test for spaces- eg Le Bargy
            $space = explode(' ', $name);
            if(count($space) > 1){

                $name = '';
                foreach($space as $k => $v){
                    $name .= ucfirst(strtolower($v)).' ';
                }

                $name = addslashes(trim($name));
                return $name;

            } else {
                if(strlen($name) <= 2){
                    return $name;
                }
                
                ///Check for Mc - eg McTighe
                $name = strtolower($name);
                            $first_two = $name{0} . (isset($name{1}) ? $name{1} : "");
                $therest = '';

                if($first_two == 'mc'){
                    for($i = 2; $i < strlen($name); $i++){
                        $therest .= $name{$i};
                    }

                    $name = "Mc".ucfirst($therest);
                    $name = addslashes(trim($name));
                    return $name;

                } else {

                    ///Check for hyphon seperated surnames
                    $hyphon = explode('-', $name);
                    if(count($hyphon) > 1){

                        $name = '';
                        $numh = 0;
                        foreach($hyphon as $k => $v){

                            $numh = $numh+1;
                            $name .= ucfirst(strtolower($v));

                            ///Dont append extra -
                            if($numh != count($hyphon)){
                                $name .= '-';
                            }

                        }

                        $name = addslashes(trim($name));
                        return $name;

                    } else {
                        ///Any other surname
                        $name = ucfirst(strtolower($name));
                        $name = addslashes(trim($name));
                        return $name;
                    }
                }
            }
        } else {
            return '';
        }
    }
}

?>