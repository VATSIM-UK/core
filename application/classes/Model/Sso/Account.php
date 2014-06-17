<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Sso_Account extends Model_Master {

    protected $_db_group = 'sso';
    protected $_table_name = 'account';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'username' => array('data_type' => 'varchar'),
        'name' => array('data_type' => 'varchar'),
        'api_key_public' => array('data_type' => 'varchar'),
        'api_key_private' => array('data_type' => 'varchar'),
        'salt' => array('data_type' => 'varchar'),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
    );
    
    // Has man relationships
    protected $_has_many = array(
        'tokens' => array(
            'model' => 'Sso_Account',
            'foreign_key' => 'sso_account_id',
        ),
    );
    
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