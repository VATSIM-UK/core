<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Account_Qualification extends Model_Master {

    protected $_db_group = 'mship';
    protected $_table_name = 'account_qualification';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'account_id' => array('data_type' => 'bigint', 'is_nullable' => FALSE),
        'type' => array('data_type' => 'string', 'is_nullable' => TRUE),
        'value' => array('data_type' => 'smallint', 'is_nullable' => TRUE),
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
        $enum = "Enum_Account_Qualification_".$this->type;
        
        // Add new rating information.
        $data[] = $enum::valueToType($this->value);
        $data[] = $enum::getDescription($this->value);
        $type = Enum_Account_Note_Type::SYSTEM;
        
        // NOW we can save!
        parent::save($validation);
        
        if($this->created != NULL && $this->removed == NULL){
            $action = "QUALIFICATION/".strtoupper($this->type)."_GRANTED";
            $date = $this->created;
        } elseif($this->removed != NULL){
            $action = "QUALIFICATION/".strtoupper($this->type)."_REVOKED";
            $date = $this->removed;
        }
        
        if(isset($action)){
            ORM::factory("Account_Note")->writeNote($this->account, $action, 707070, $data, $type, $date);
       }
    }
    
    /**
     * Add a VATSIM Style ATC rating.
     * 
     * @param Model_Account_Main $account The account to add to.
     * @param integer $vatrating the VATSIM rating.
     */
    public function addATCQualification($account, $vatrating, $dateOverride=null){
        // First of all, is an account loaded?
        if(!$account->loaded()){
            return false;
        }
        
        // Convert this vatrating into a system rating.
        $sysRating = Helper_Account_Qualification::convert_vatsim_atc_rating($vatrating);
        
        if(Arr::get($sysRating, 0, null) == null OR Arr::get($sysRating, 1, null) == null){
            return false;
        }
        
        // Expired training/admin ratings should be removed.
        // ATC expired - only if downgraded.  Shouldn't ever happen, but *could*.
        if(strcasecmp($sysRating[0], "ATC") == 0){
            // If ratings are higher than current, they just delete "deleted".
            /*foreach($account->qualifications->get_all_atc() as $r){
                if($r->value > $sysRating[1] && strtotime($dateOverride) >= $r->created){
                    $r->delete($dateOverride);
                }
            }*/
        }
        
        // ATC Training expired
        foreach($account->qualifications->get_all_training_atc() as $r){
            if(($r->value != $sysRating[1] AND strcasecmp($sysRating[0], "Training_ATC") == 0) OR strcasecmp($sysRating[0], "Training_ATC") != 0){
                $r->delete($dateOverride);
            }
        }
        
        // Admin expired
        foreach($account->qualifications->get_all_admin() as $r){
            if(($r->value != $sysRating[1] AND strcasecmp($sysRating[0], "Admin") == 0) OR strcasecmp($sysRating[0], "Admin") != 0){
                $r->delete($dateOverride);
            }
        }

        // Next, if this qualification has already been earnt - ignore it!
        $check = $account->qualifications->where("type", "=", $sysRating[0]);
        $check = $check->where("value", "=", $sysRating[1]);
        $check = $check->where("removed", "IS", NULL);
        if($check->reset(FALSE)->count_all() > 0){
            return false;
        }

        // Add this rating, biatch!
        // We've got the all clear that it's not in existance.  No life on Mars, NASA would say.
        $newRating = ORM::factory("Account_Qualification");
        $newRating->account_id = $account;
        $newRating->type = $sysRating[0];
        $newRating->value = $sysRating[1];
        $newRating->created = (($dateOverride == null) ? gmdate("Y-m-d H:i:s") : $dateOverride);
        $newRating->save();
        return true;
    }
    
    /**
     * Add a VATSIM Style Pilot rating.
     * 
     * @param Model_Account_Main $account The account to add to.
     * @param integer $prating The pilot rating to add.
     */
    public function addPilotQualification($account, $prating, $dateOverride=null){
        // First of all, is an account loaded?
        if(!$account->loaded()){
            return false;
        }

        // Next, if this qualification has already been earnt - ignore it!
        $check = $account->qualifications->where("type", "=", "Pilot");
        $check = $check->where("value", "=", $prating);
        $check = $check->where("removed", "IS", NULL);
        if($check->reset(FALSE)->count_all() > 0){
            return false;
        }
        
        // Add this rating, biatch!
        // We've got the all clear that it's not in existance.  No life on Mars, NASA would say.
        $newRating = ORM::factory("Account_Qualification");
        $newRating->account_id = $account;
        $newRating->type = "Pilot";
        $newRating->value = $prating;
        $newRating->created = (($dateOverride == null) ? gmdate("Y-m-d H:i:s") : $dateOverride);
        $newRating->save();
        
        return true;
    }
    
    // Get the text for the endorsement
    public function __toString(){
        return $this->formatQualification(false);
    }
    
    // Format the current qualification.
    public function formatQualification($full=true){
        $enum = "Enum_Account_Qualification_".$this->type;
        return $full ? $enum::getDescription($this->value) : $enum::valueToType($this->value); 
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
    
    // Determine if the account has the rating being checked.
    public function check_has_qualification($type="atc", $value, $incDeleted=false){
        $quals = $this->{"get_all_".$type}($incDeleted);
        foreach($quals as $qual){
            if($qual->value == $value){
                return true;
            }
        }
        return false;
    }
    
    // Pre-get_**
    private function helper_pre_get_all($incDeleted=false, $orderBy="value", $orderByDir="DESC"){
        if($incDeleted){
            return $this->order_by($orderBy, $orderByDir);
        } else {
            return $this->where("removed", "IS", NULL)->order_by($orderBy, $orderByDir);
        }
    }
    private function helper_pre_get_current(){
        return $this->helper_pre_get_all()->limit(1);
    }
    
    // Get the current atc qualification.
    public function get_current_atc(){
        return $this->helper_pre_get_current()->where("type", "LIKE", "atc")->find();
    }
    
    public function get_all_atc($incDeleted=false, $orderBy="value", $orderByDir="DESC"){
        return $this->helper_pre_get_all($incDeleted, $orderBy, $orderByDir)->where("type", "LIKE", "atc")->find_all();
    }
    
    public function get_current_training_atc(){
        return $this->helper_pre_get_current()->where("type", "LIKE", "training_atc")->find();
    }
    
    public function get_all_training_atc($incDeleted=false, $orderBy="value", $orderByDir="DESC"){
        return $this->helper_pre_get_all($incDeleted, $orderBy, $orderByDir)->where("type", "LIKE", "training_atc")->find_all();
    }
    
    public function get_current_pilot(){
        return $this->helper_pre_get_current()->where("type", "LIKE", "pilot")->find();
    }
    
    public function get_all_pilot($incDeleted=false, $orderBy="value", $orderByDir="DESC"){
        return $this->helper_pre_get_all($incDeleted, $orderBy, $orderByDir)->where("type", "LIKE", "pilot")->find_all();
    }
    
    public function get_current_training_pilot(){
        return $this->helper_pre_get_current()->where("type", "LIKE", "training_pilot")->find();
    }
    
    public function get_all_training_pilot($incDeleted=false, $orderBy="value", $orderByDir="DESC"){
        return $this->helper_pre_get_all($incDeleted, $orderBy, $orderByDir)->where("type", "LIKE", "training_pilot")->find_all();
    }
    
    public function get_current_admin(){
        return $this->helper_pre_get_current()->where("type", "LIKE", "admin")->find();
    }
    
    public function get_all_admin($incDeleted=false, $orderBy="value", $orderByDir="DESC"){
        return $this->helper_pre_get_all($incDeleted, $orderBy, $orderByDir)->where("type", "LIKE", "admin")->find_all();
    }
    
    /**
     * All all UK administrative/training ratings (Instructors).
     * 
     * @param string $type Either pilot or atc.
     * @return Model_Account_Qualification 
     */
    public function get_all_training($type="pilot", $incDeleted=false){
        return $this->{"get_all_training_".$type}($incDeleted);
    }
    public function get_current_training($type="pilot"){
        return $this->{"get_current_training_".$type}();
    }
}

?>