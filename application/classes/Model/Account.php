<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Account extends Model_Master {

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
                array('ucfirst'),
                array(array("UTF8", "clean"), array(":value")),
                array(array($this, "formatName"), array(":value", "f")),
            ),
            'name_last' => array(
                array('trim'),
                array('ucfirst'),
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
    
    // Format name
    public function formatName($name, $type='f'){
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
        
    public function get_last_login_ip(){
        return long2ip($this->last_login_ip);
    }
    
    public function count_last_login_ip_usage($ip, $timeLimit="-8 hours"){
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
}

?>