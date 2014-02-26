<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Training_Theory_Category extends Model_Master {

    protected $_db_group = 'train';
    protected $_table_name = 'theory_category';
    protected $_table_columns = array(
        'id' => array('data_type' => 'int'),
        'name' => array('data_type' => 'string'),
        'available' => array('data_type' => 'boolean'),
        'deleted' => array('data_type' => 'boolean'),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
    );
    
    // Has many relationships
    protected $_has_many = array(
        'questions' => array(
            'model' => 'Training_Theory_Question',
            'foreign_key' => 'category_id',
        ),
        'test_categories' => array(
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