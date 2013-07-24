<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Account_Note_Format extends Model_Master {

    protected $_db_group = 'mship';
    protected $_table_name = 'account_note_format';
    protected $_table_columns = array(
        'id' => array('data_type' => 'mediumint'),
        'section' => array('data_type' => 'varchar'),
        'action' => array('data_type' => 'varchar'),
        'string' => array('data_type' => 'varchar'),
        'version' => array('data_type' => 'smallint'),
        'created' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
        'note' => array(
            'model' => 'Account_Note',
            'foreign_key' => 'format_id',
        ),
    );
    
    // Has man relationships
    protected $_has_many = array();
    
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