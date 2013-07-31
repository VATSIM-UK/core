<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Account_Qualification extends Model_Master {

    protected $_db_group = 'mship';
    protected $_table_name = 'account_qualification';
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
        return $this->formatQualification(false);
    }
    
    // Format the current qualification.
    public function formatQualification($full=true){
        $enum = "Enum_Account_Qualification_".$this->type;
        return $full ? $enum::getDescription($this->value) : $enum::idToType($this->value); 
    }
    
    // Get position suffixes.
    public function formatPositionSuffixes($type="string"){
        // Only ATC!
        if($this->type != "ATC"){
            return "";
        }
        
        $enum = "Enum_Account_Qualification_ATC";
        return ($type == "string") ? $enum::getPositionSuffixes($this->value) : explode(",", $enum::getPositionSuffixes($this->value)); 
    }
    
    // Pre-get_**
    private function helper_pre_get_all(){
        return $this->where("removed", "IS", NULL)->order_by("value", "DESC");
    }
    private function helper_pre_get_current(){
        return $this->helper_pre_get_all()->limit(1);
    }
    
    // Get the current atc qualification.
    public function get_current_atc(){
        return $this->helper_pre_get_current()->where("type", "LIKE", "atc")->find();
    }
    
    public function get_all_atc(){
        return $this->helper_pre_get_all()->where("type", "LIKE", "atc")->find_all();
        
        // Turn into a readable array....
        $return = array();
        foreach($quals as $qual){
            $return[] = $qual->value;
        }
        
        // Yeah, we're done.... phew!
        return $return;
    }
    
    public function get_current_pilot(){
        return $this->helper_pre_get_all()->where("type", "LIKE", "pilot")->find();
    }
    
    public function get_all_pilot(){
        return $this->helper_pre_get_all()->where("type", "LIKE", "pilot")->find_all();
        
        // Turn into a readable array....
        $return = array();
        foreach($quals as $qual){
            $return[] = $qual->value;
        }
        
        // Yeah, we're done.... phew!
        return $return;
        
    }
}

?>