<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Stats_Pilot extends Model_Master {

    protected $_db_group = 'stats';
    protected $_table_name = 'pilot';
    protected $_primary_key = 'id';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'account_id' => array('data_type' => 'int'),
        'callsign' => array('data_type' => 'varchar'),
        'departure' => array('data_type' => 'varchar'),
        'arrival' => array('data_type' => 'varchar'),
        'alternative' => array('data_type' => 'varchar'),
        'cruise' => array('data_type' => 'int'),
        'posrep_count' => array('data_type' => 'int'),
        'posrep_latest' => array('data_type' => 'bigint'),
        'status' => array('data_type' => 'int'),
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
        'posreps' => array(
            'model' => 'Stats_Pilot_Posrep',
            'foreign_key' => 'pilot_session_id',
        ),
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
    
    public function latest_posrep(){
        return ORM::factory("Stats_Pilot_Posrep", $this->posrep_latest);
    }
    
    public function calculate_status(){
        if(!$this->loaded()){ return Enum_Stats_Flight_Status::UNKNOWN; }
        
        // We need to work out where we can go from the current status FIRST.
        switch($this->status){
            // Transition from UNKNOWN to PRE_FLIGHT
            case Enum_Stats_Flight_Status::UNKNOWN:
                if($this->latest_posrep()->groundspeed < 1){
                    return Enum_Stats_Flight_Status::PRE_FLIGHT;
                    break;
                }
            
            // Transition from UNKNOWN or PRE_FLIGHT to TAXI_OUT
            case Enum_Stats_Flight_Status::UNKNOWN:
            case Enum_Stats_Flight_Status::PRE_FLIGHT:
                if(Helper_Comparison::between_both_incl($this->latest_posrep()->groundspeed, 5, 40)){
                    return Enum_Stats_Flight_Status::TAXI_OUT;
                    break;
                }
                
            // Transition from Unknown OR TAXI_OUT to DEPARTING
            case Enum_Stats_Flight_Status::UNKNOWN:
            case Enum_Stats_Flight_Status::TAXI_OUT:
                if(Helper_Comparison::between_not_incl($this->latest_posrep()->groundspeed, 40, 200)){
                    return Enum_Stats_Flight_Status::DEPARTING;
                    break;
                }
                
            // Transition from UNKNOWN or DEPARTING OR DESCENDING OR CRUISE to CLIMB
            case Enum_Stats_Flight_Status::UNKNOWN:
            case Enum_Stats_Flight_Status::DEPARTING:
            case Enum_Stats_Flight_Status::CRUISE:
            case Enum_Stats_Flight_Status::DESCENT:
                if($this->latest_posrep()->groundspeed >= 200 && $this->latest_posrep()->vertical_speed > 300){
                    return Enum_Stats_Flight_Status::CLIMB;
                    break;
                }
                
            // Transition from UNKNOWN or CLIMB OR DESCENDING to CRUISE
            case Enum_Stats_Flight_Status::UNKNOWN:
            case Enum_Stats_Flight_Status::CLIMB:
            case Enum_Stats_Flight_Status::DESCENT:
                if($this->latest_posrep()->groundspeed >= 250 && Helper_Comparison::between_both_incl($this->latest_posrep()->vertical_speed, -300, 300)){
                    return Enum_Stats_Flight_Status::CRUISE;
                    break;
                }
                
            // Transition from UNKNOWN or CLIMB or CRUISE to DESCENT
            case Enum_Stats_Flight_Status::UNKNOWN:
            case Enum_Stats_Flight_Status::CLIMB:
            case Enum_Stats_Flight_Status::CRUISE:
                if($this->latest_posrep()->vertical_speed < -300){
                    return Enum_Stats_Flight_Status::DESCENT;
                    break;
                }
                
            // Transition from UNKNOWN or DESCENT to APPROACH
            case Enum_Stats_Flight_Status::UNKNOWN:
            case Enum_Stats_Flight_Status::DESCENT:
                if($this->latest_posrep()->groundspeed <= 250 && $this->latest_posrep()->altitude <= min($this->cruise-10000, $this->cruise-1000)){
                    return Enum_Stats_Flight_Status::APPROACH;
                    break;
                }
                
            // Transition from UNKNOWN or APPROACH to TAXI_IN
            case Enum_Stats_Flight_Status::UNKNOWN:
            case Enum_Stats_Flight_Status::APPROACH:
                if(Helper_Comparison::between_both_incl($this->latest_posrep()->groundspeed, 5, 40)){
                    return Enum_Stats_Flight_Status::TAXI_IN;
                    break;
                }
                
            // Transition from UNKNOWN or TAXI_IN to ARRIVED
            case Enum_Stats_Flight_Status::UNKNOWN:
            case Enum_Stats_Flight_Status::TAXI_IN:
                if($this->latest_posrep()->groundspeed < 5){
                    return Enum_Stats_Flight_Status::ARRIVED;
                    break;
                }
        }
    }
    
    public function run_expiration($timestampBefore){
        $expired = ORM::factory("Stats_Pilot")->where("updated_time", "<", $timestampBefore)->where("logoff_time", "IS", NULL)->find_all();
        foreach($expired as $exp){
            $exp->logoff_time = $timestampBefore;
            $exp->save();
        }
    }
}

?>