<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Account extends Model_Master {

    protected $_db_group = 'mship';
    protected $_table_name = 'account';
    protected $_primary_key = 'id';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'token' => array('data_type' => 'string', 'is_nullable' => FALSE),
        'token_ip' => array('data_type' => 'int', 'is_nullable' => FALSE),
        'name_first' => array('data_type' => 'string', 'is_nullable' => FALSE),
        'name_last' => array('data_type' => 'string', 'is_nullable' => FALSE),
        'password' => array('data_type' => 'string', 'is_nullable' => FALSE),
        'extra_password' => array('data_type' => 'string', 'is_nullable' => FALSE),
        'gender' => array('data_type' => 'char', 'is_nullable' => TRUE),
        'age' => array('data_type' => 'smallint', 'is_nullable' => FALSE),
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
            'token_ip' => array(
                array("ip2long"),
            ),
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
    
    public function get_atc_qualification(){
        // must be an instance of a user
        if (!$this->loaded()){
             throw new Kohana_Exception("ATC Qualification cannot be found for a non-user");
             return false;
        }
        
        return $this->qualifications->where("removed", "IS", NULL)
                                          ->where("type", "=", "atc")
                                          ->order_by("value", "DESC")->limit(1)
                                          ->find()->value;
        
    }
    
    public function get_pilot_qualifications(){
        // must be an instanceof a user
        if (!$this->loaded()){
             throw new Kohana_Exception("Pilot Qualifications cannot be found for a non-user");
             return false;
        }
        
        $quals = $this->qualifications->where("removed", "IS", NULL)
                                          ->where("type", "=", "pilot")
                                          ->order_by("value", "DESC")->limit(1)
                                          ->find_all();
        $return = array();
        foreach($quals as $qual){
            $return[] = $qual->value;
        }
        
        return $return;
        
    }
    
    
}

?>