<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Training_Theory_Attempt_Question extends Model_Master {

    protected $_db_group = 'train';
    protected $_table_name = 'theory_attempt';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'attempt_id' => array('data_type' => 'int'),
        'question_id' => array('data_type' => 'int'),
        'time_start' => array('data_type' => 'timestamp'),
        'time_end' => array('data_type' => 'timestamp'),
        'answer' => array('data_type' => 'string'),
        'result' => array('data_type' => 'tinyint'),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
        'attempt_id' => array(
            'model' => 'Training_Theory_Attempt_Question',
            'foreign_key' => 'attempt_id',
        ),
        'question' => array(
            'model' => 'Training_Theory_Question',
            'foreign_key' => 'question_id',
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