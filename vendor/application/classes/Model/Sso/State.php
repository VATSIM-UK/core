<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Sso_Token extends Model_Master {

    protected $_db_group = 'sso';
    protected $_table_name = 'token';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'token' => array('data_type' => 'varchar'),
        'created' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'expires' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
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
}

?>