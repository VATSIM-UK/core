<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Stats_Controller extends Model_Master {

    protected $_db_group = 'stats';
    protected $_table_name = 'controller';
    protected $_primary_key = 'id';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'account_id' => array('data_type' => 'int'),
        'callsign' => array('data_type' => 'string'),
        'frequency' => array('data_type' => 'decimal'),
        'latitude' => array('data_type' => 'decimal'),
        'longitude' => array('data_type' => 'decimal'),
        'visual_range' => array('data_type' => 'int'),
        'server' => array('data_type' => 'varchar'),
        'logon_time' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'updated_time' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'logoff_time' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );

    // Belongs to relationships
    protected $_belongs_to = array(
        'account' => array(
            'model' => 'Account',
            'foreign_key' => 'account_id',
        ),
    );
    
    // Has man relationships
    protected $_has_many = array(
    );
    
    // Has one relationship
    protected $_has_one = array(
    );
    
    // Validation rules
    public function rules(){
        return array(
        );
    }
    
    // Data filters
    public function filters(){
        return array(
        );
    }
    
    /**
     * @override
     */
    public function save(\Validation $validation = NULL) {
        $this->updated_time = gmdate("Y-m-d H:i:s");
        parent::save($validation);
        
        return true;
    }
}

?>