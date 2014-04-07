<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Stats_Pilot_Posrep extends Model_Master {

    protected $_db_group = 'stats';
    protected $_table_name = 'pilot_posrep';
    protected $_primary_key = 'id';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'pilot_session_id' => array('data_type' => 'int'),
        'latitude' => array('data_type' => 'decimal'),
        'longitude' => array('data_type' => 'decimal'),
        'altitude' => array('data_type' => 'int'),
        'vertical_speed' => array('data_type' => 'decimal'),
        'groundspeed' => array('data_type' => 'int'),
        'transponder' => array('data_type' => 'int'),
        'heading' => array('data_type' => 'int'),
        'qnh_mb' => array('data_type' => 'decimal'),
        'status' => array('data_type' => 'int'),
        'timestamp' => array('data_type' => 'timestamp', "is_nullable" => true),
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
        'pilot_session' => array(
            'model' => 'Stats_Pilot',
            'foreign_key' => 'pilot_session_id',
        )
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
}

?>