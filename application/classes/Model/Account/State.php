<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Account_State extends Model_Master {

    protected $_db_group = 'mship';
    protected $_table_name = 'account_state';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'account_id' => array('data_type' => 'bigint'),
        'state' => array('data_type' => 'tinyint'),
        'created' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'removed' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
        'account' => array(
            'model' => 'Account_Main',
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
        
    // Get the text for the endorsement
    public function __toString(){
        return $this->formatEndorsement(false);
    }
    public function formatState($full=true){
        return $full ? Enum_Account_State::getDescription($this->value) : Enum_Account_State::valueToType($this->value); 
    }
}

?>