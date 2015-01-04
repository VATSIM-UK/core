<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Training_Course_Prerequisite extends Model_Master {

    protected $_db_group = 'train';
    protected $_table_name = 'course_prerequisite';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'course_id' => array('data_type' => 'int'),
        'type' => array('data_type' => 'string'),
        'operator' => array('data_type' => 'string'),
        'value' => array('data_type' => 'string'),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
        'course' => array(
            'model' => 'Training_Course',
            'foreign_key' => 'course_id',
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