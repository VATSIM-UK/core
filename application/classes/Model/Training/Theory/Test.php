<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Training_Theory_Test extends Model_Master {

    protected $_db_group = 'train';
    protected $_table_name = 'theory_test';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'name' => array('data_type' => 'string'),
        'time_allowed' => array('data_type' => 'smallint'),
        'time_expire_action' => array('data_type' => 'string'),
        'retake_cooloff' => array('data_type' => 'tinyint'),
        'retake_max' => array('data_type' => 'tinyint'),
        'available' => array('data_type' => 'boolean'),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
    );
    
    // Has many relationships
    protected $_has_many = array(
        'attempts' => array(
            'model' => 'Training_Theory_Attempt',
            'foreign_key' => 'test_id',
        ),
        'categories' => array(
            'model' => 'Training_Theory_Test_Category',
            'foreign_key' => 'category_id',
        ),
    );
    
    // Has one relationship
    protected $_has_one = array(
    );
    
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