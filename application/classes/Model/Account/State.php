<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Account_State extends Model_Master {

    protected $_db_group = 'mship';
    protected $_table_name = 'account_state';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'account_id' => array('data_type' => 'bigint'),
        'state' => array('data_type' => 'tinyint'),
        'created' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'removed' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
        'account' => array(
            'model' => 'Account_Main',
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
    
    /**
     * @overload
     */
    public function delete($dateOverride=null){
        $this->removed = (($dateOverride == null) ? gmdate("Y-m-d H:i:s") : $dateOverride);
        $this->save();
    }
    
    /**
     * @overload
     */
    public function save(\Validation $validation = NULL) {
        // Start getting the note data ready!
        $data = array();
        $oldState = ORM::factory("Account_Main", $this->account_id)->states->getCurrent();
        
        if($oldState->loaded()){
            $data[] = $oldState->formatState(false);
            $data[] = $oldState->formatState(true);
        } else {
            $data[] = Enum_Account_State::NOT_REGISTERED;
            $data[] = Enum_Account_State::getDescription(Enum_Account_State::NOT_REGISTERED);
        }
        
        $type = Enum_Account_Note_Type::SYSTEM;
        
        // NOW we can save!
        parent::save($validation);
        
        $action = "STATE/CHANGED";
        if($this->created != NULL && $this->removed == NULL){
            $date = $this->created;
        } elseif($this->removed != NULL){
            $date = $this->removed;
        }
        
        if(isset($action)){
            ORM::factory("Account_Note")->writeNote($this->account, $action, 707070, $data, $type, $date);
       }
    }
    
    /**
     * Add a new state for this member.
     * 
     * @param Model_Account_Main $account The account to add to.
     * @param integer $state The State to set.
     * @param timestamp $dateOverride If this is set, this date will be used.
     */
    public function addState($account, $state, $dateOverride=null){
        // First of all, is an account loaded?
        if(!$account->loaded()){
            return false;
        }
        
        // Get the current state, if it's the same as now we'll ignore this request!
        $currentState = $account->states->getCurrent();
        if($currentState->loaded() && $currentState->state == Enum_Account_State::IdToValue($state)){
            return false;
        }
        
        // Remmove all current states.
        foreach($account->states->find_all() as $r){
            $r->delete();
        }

        // Add this state!
        $newState = ORM::factory("Account_State");
        $newState->account_id = $account;
        $newState->state = Enum_Account_State::IdToValue($state);
        $newState->created = (($dateOverride == null) ? gmdate("Y-m-d H:i:s") : $dateOverride);
        $newState->save();
        return true;
    }
    
    public function getCurrent(){
        return $this->where("removed", "IS", NULL)->limit(1)->find();
    }
        
    // Get the text for the endorsement
    public function __toString(){
        return $this->formatEndorsement(false);
    }
    public function formatState($full=true){
        return $full ? Enum_Account_State::getDescription($this->state) : Enum_Account_State::valueToType($this->state); 
    }
}

?>