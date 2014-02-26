<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Training_Theory_Test_Category extends Model_Master {

    protected $_db_group = 'train';
    protected $_table_name = 'theory_test_category';
    protected $_table_columns = array(
        'id' => array('data_type' => 'int'),
        'test_id' => array('data_type' => 'int'),
        'category_id' => array('data_type' => 'int'),
        'difficulty_min' => array('data_type' => 'int'),
        'difficulty_max' => array('data_type' => 'int'),
        'question_count' => array('data_type' => 'smallint'),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
        'category' => array(
            'model' => 'Training_Theory_Category',
            'foreign_key' => 'category_id',
        ),
        'test' => array(
            'model' => 'Training_Theory_Test',
            'foreign_key' => 'test_id',
        ),
    );
    
    // Has many relationships
    protected $_has_many = array(
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