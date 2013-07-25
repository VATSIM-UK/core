<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Training_Theory extends Model_Master {

    protected $_db_group = 'train';
    protected $_table_name = 'theory';
    protected $_primary_key = 'test_id';
    protected $_table_columns = array(
        'test_id' => array('data_type' => 'mediumint'),
        'test_name' => array('data_type' => 'varchar'),
        'atc_pilot' => array('data_type' => 'smallint'),
        'min_qualification' => array('data_type' => 'smallint'),
        'test_type' => array('data_type' => 'smallint'),
        'question_config' => array('data_type' => 'text'),
        'require_approval_start' => array('data_type' => 'smallint'),
        'require_approval_pass' => array('data_type' => 'smallint'),
        'automatic_retake' => array('data_type' => 'smallint'),
        'available' => array('data_type' => 'smallint'),
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
        );
    }
    
    // Get the text for the note
    public function __toString(){
        return $this->body;
    }
    
    public function mayTake($account){
         
          // must be a theory test selected
          if (!$this->loaded()){
               throw new Kohana_Exception("may only take a theory test that exists");
               return false;
          }
         
          // If account isn't of type Model_Account, error.
          if(!$account instanceof Model_Account){
              throw new Kohana_Exception("'account' must be of type Model_Account");
              return false;
          }
          
          // check the user has the correct endorsement
          //TODO
          
          return true;
          
    }
    
}

?>