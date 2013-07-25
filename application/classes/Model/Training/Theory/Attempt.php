<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Training_Theory_Attempt extends Model_Master {

    protected $_db_group = 'train';
    protected $_table_name = 'theory_attempt';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'test_id' => array('data_type' => 'mediumint'),
        'account_id' => array('data_type' => 'bigint'),
        'question_config' => array('data_type' => 'text'),
        'questions' => array('data_type' => 'text'),
        'result' => array('data_type' => 'smallint'),
        'start_time' => array('data_type' => 'datetime'),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
        'test' => array(
            'model' => 'Training_Theory_Test', 
            'foreign_key' => 'test_id'
        ),
        'account' => array(
            'model' => 'Account',
            'foreign_key' => 'account_id'
        )
    );
    
    // Has man relationships
    protected $_has_many = array();
    
    // Has one relationship
    protected $_has_one = array(
        
    );
    
    // Validation rules
    public function rules(){
        return array();
    }
    
    // Data filters
    public function filters(){
        return array(
            'question_config' => array(
                array('json_encode'),
            ),
            'questions' => array(
                array('json_encode')
            )
        );
    }
    
    // Get the text for the note
    public function __toString(){
        return $this->body;
    }
    
}

?>