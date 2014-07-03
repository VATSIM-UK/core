<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Stats_Controller extends Model_Master {

    protected $_db_group = 'stats';
    protected $_table_name = 'controller';
    protected $_primary_key = 'id';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'account_id' => array('data_type' => 'int'),
        'callsign' => array('data_type' => 'varchar'),
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
    
    public function find_recent($cid, $callsign){
        $check = ORM::factory("Stats_Controller")->where("account_id", "=", $cid);
        $check = $check->where("callsign", "LIKE", $callsign);
        $check = $check->where("updated_time", ">=", gmdate("Y-m-d H:i:s", strtotime("-4 minutes")));
        $check = $check->find();
        
        // Run the check!
        if($check->loaded()){
            return $check;
        } else {
            return ORM::factory("Stats_Controller");
        }
    }
    
    public function run_expiration($timestampBefore){
        $expired = ORM::factory("Stats_Controller")->where("updated_time", "<", $timestampBefore)->where("logoff_time", "IS", NULL)->find_all();
        foreach($expired as $exp){
            $exp->logoff_time = $timestampBefore;
            $exp->save();
        }
    }
}

?>