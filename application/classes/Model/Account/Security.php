<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Account_Security extends Model_Master {

    protected $_db_group = 'mship';
    protected $_table_name = 'account_security';
    protected $_table_columns = array(
        'id' => array('data_type' => 'int'),
        'account_id' => array('data_type' => 'int'),
        'type' => array('data_type' => 'smallint'),
        'value' => array('data_type' => 'varchar'),
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
        return array(
            'value' => array(
                array(array($this, "validatePassword")),
            ),
        );
    }
    
    // Data filters
    public function filters(){
        return array();
    }
    
    // Validate the passwords
    public function validatePassword($password){
        // Create the name of the enum class
        $enum = "Enum_Account_Security_".ucfirst(strtolower(Enum_Account_Security::idToType($this->type)));
        
        // Does it meet the minimum length?
        if($enum::MIN_LENGTH > 0){
            if(strlen($password) < $enum::MIN_LENGTH){
                return false;
            }
        }
        
        // Minimal alphabetic characters?
        if($enum::MIN_ALPHA > 0){
            preg_match_all("/[a-zA-Z]/", $password, $matches);
            $matches = isset($matches[0]) ? $matches[0] : $matches;
            if(count($matches) < $enum::MIN_ALPHA){
                return false;
            }
        }
        
        // Minimal numeric characters?
        if($enum::MIN_NUMERIC > 0){
            preg_match_all("/[0-9]/", $password, $matches);
            $matches = isset($matches[0]) ? $matches[0] : $matches;
            if(count($matches) < $enum::MIN_NUMERIC){
                return false;
            }
        }
        
        // Minimal non-alphanumeric
        if($enum::MIN_NON_ALPHANUM > 0){
            preg_match_all("/[^a-zA-Z0-9]/", $password, $matches);
            $matches = isset($matches[0]) ? $matches[0] : $matches;
            if(count($matches) < $enum::MIN_NON_ALPHANUM){
                return false;
            }
        }
        
        $this->value = sha1(sha1($this->value));
        return true;
    }
    
    // Save the new password
    public function save(){// Let's just update the expiry!
        $enum = "Enum_Account_Security_".ucfirst(strtolower(Enum_Account_Security::idToType($this->type)));
        $this->expires = gmdate("Y-m-d H:i:s", strtotime("+".$enum::MIN_LIFE." days"));
        $this->created = gmdate("Y-m-d H:i:s");
        parent::save();
    }
}

?>