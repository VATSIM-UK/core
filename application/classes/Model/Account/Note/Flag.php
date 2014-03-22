<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Account_Note_Flag extends Model_Master {

    protected $_db_group = 'mship';
    protected $_table_name = 'account_note_flag';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'flag_by' => array('data_type' => 'bigint'),
        'flag_time' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'flag_comment' => array('data_type' => 'varchar'),
        'resolve_by' => array('data_type' => 'bigint', 'is_nullable' => TRUE),
        'resolve_time' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'resolve_comment' => array('data_type' => 'varchar'),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
        'flagger' => array(
            'model' => 'Account_Main',
            'foreign_key' => 'flag_by',
        ),
        'resolver' => array(
            'model' => 'Account_Main',
            'foreign_key' => 'resolve_by',
        ),
        'note' => array(
            'model' => 'Account_Note',
            'foreign_key' => 'flag_id',
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
    
    // Get the text for the endorsement
    public function __toString(){
        return null;
    }
}

?>