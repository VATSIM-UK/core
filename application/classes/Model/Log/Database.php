<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Log_Database extends Model_Master {

    protected $_db_group = 'sys';
    protected $_table_name = 'log';
    protected $_primary_key = 'id';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'level' => array('data_type' => 'smallint'),
        'body' => array('data_type' => 'string'),
        'trace' => array('data_type' => 'string'),
        'file' => array('data_type' => 'string'),
        'line' => array('data_type' => 'smallint'),
        'class' => array('data_type' => 'string'),
        'function' => array('data_type' => 'string'),
        'additional' => array('data_type' => 'string'),
        'time' => array('data_type' => 'int'),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );

    // Belongs to relationships
    protected $_belongs_to = array();
    
    // Has man relationships
    protected $_has_many = array(
    );
    
    // Has one relationship
    protected $_has_one = array();
    
    // Validation rules
    public function rules(){
        return array(
        );
    }
    
    // Data filters
    public function filters(){
        return array(
            'trace' => array(
                array('serialize'),
            ),
            'additional' => array(
                array('serialize'),
            ),
        );
    }
}

?>