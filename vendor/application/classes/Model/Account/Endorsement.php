<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Account_Endorsement extends Model_Master {

    protected $_db_group = 'mship';
    protected $_table_name = 'account_endorsement';
    protected $_table_columns = array(
        'account_id' => array('data_type' => 'bigint', 'is_nullable' => FALSE),
        'type' => array('data_type' => 'string', 'is_nullable' => TRUE),
        'value' => array('data_type' => 'smallint', 'is_nullable' => TRUE),
        'created' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
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
        return $this->formatEndorsement(false);
    }
    public function formatEndorsement($full=true){
        $enum = "Enum_Account_Endorsements_".$this->type;
        return $full ? $enum::getDescription($this->value) : $enum::idToType($this->value); 
    }
    
    // Get position suffixes.
    public function formatPositionSuffixes($type="string"){
        // Only ATC!
        if($this->type != "ATC"){
            return "";
        }
        
        $enum = "Enum_Account_Endorsements_ATC";
        return ($type == "string") ? $enum::getPositionSuffixes($this->value) : explode(",", $enum::getPositionSuffixes($this->value)); 
    }
}

?>