<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Training_Outcome extends Model_Master {

    protected $_db_group = 'train';
    protected $_table_name = 'outcome';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'name' => array('data_type' => 'varchar'),
        'enabled' => array('data_type' => 'boolean'),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
    );
    
    // Has man relationships
    protected $_has_many = array(
        'theory_course' => array(
            'model' => 'Train_Course',
            'foreign_key' => 'theory_outcome_id',
        ),
        'practical_course' => array(
            'model' => 'Train_Course',
            'foreign_key' => 'practical_outcome_id',
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
    
    // Default/stadard output to string.
    public function __toString() {
        return $this->name;
    }
}

?>