<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Training_Course extends Model_Master {

    protected $_db_group = 'train';
    protected $_table_name = 'course';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'code' => array('data_type' => 'varchar'),
        'name' => array('data_type' => 'varchar'),
        'overview' => array('data_type' => 'varchar'),
        'theory_status' => array('data_type' => 'boolean'),
        'theory_overview' => array('data_type' => 'varchar'),
        'theory_outcome_id' => array('data_type' => 'smallint'),
        'practical_status' => array('data_type' => 'boolean'),
        'practical_overview' => array('data_type' => 'varchar'),
        'practical_outcome_id' => array('data_type' => 'smallint'),
        'enabled' => array('data_type' => 'boolean'),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
    );
    
    // Has many relationships
    protected $_has_many = array(
    );
    
    // Has one relationship
    protected $_has_one = array(
        'theory_outcome' => array(
            'model' => 'Training_Outcome',
            'foreign_key' => 'theory_outcome_id',
        ),
        'practical_outcome' => array(
            'model' => 'Training_Outcome',
            'foreign_key' => 'practical_outcome_id',
        )
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
        return $this->name ." (".$this->code.")";
    }
    
    /**
     * Get all active course from the database.
     * 
     * A course is defined as active if it has it's enabled flag set to TRUE.
     * 
     * @return array An array of all active courses.
     */
    public function getActive(){
        return $this->where("enabled", "=", "1")->find_all();
    }
}

?>