<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Account_Email extends Model_Master {

    protected $_table_name = 'account_email';
    protected $_db_group = 'mship';
    protected $_primary_key = 'id';
    protected $_table_columns = array(
        'id' => array('data_type' => 'string'),
        'account_id' => array('data_type' => 'bigint'),
        'email' => array('data_type' => 'string'),
        'primary' => array('data_type' => 'boolean', 'is_nullable' => TRUE),
        'verified' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'created' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'deleted' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
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
    protected $_has_many = array(
        'sso_email' => array(
            'model' => 'Sso_Email',
            'foreign_key' => 'account_email_id',
        ),
    );
    
    // Has one relationship
    protected $_has_one = array(
    );

    // Validation rules
    public function rules() {
        return array(
            'email' => array(
                array('not_empty'),
                array('email', array(':value', true)),
                //array(array($this, 'email_check_unique')),
            ),
        );
    }

    // Data filters
    public function filters() {
        return array(
            'email' => array(
                array('trim'),
                array('strtolower'),
            )
        );
    }
    
    /**
     * Add a new email to the given account.
     * 
     * @param object $account The account to add the email to.
     * @param string $email The email address to add.
     * @param boolean|int If set to TRUE will be verified straight away.
     * @param boolean|int $primary If set to TRUE or 1, then set to primary!
     */
    public function action_add_email($account, $email, $verify=0, $primary=0){
        if(!$account->loaded()){
            return;
        }
        
        // Let's check for this email on this account.
        if($account->emails->where("email", "LIKE", $email)->count_all() > 0){
            return;
        }
        
        // Create new.
        try {
            $newEmail = ORM::factory("Account_Email");
            $newEmail->account_id = $account;
            $newEmail->email = $email;
            $newEmail->verified = ($verify ? gmdate("Y-m-d H:i:s") : NULL);
            $newEmail->created = gmdate("Y-m-d H:i:s");
            $newEmail->save();
        } catch(ORM_Validation_Exception $e){
            print_r($e->errors());
            return;
        }
        
        // Log it!
        $data = array($email);
        ORM::factory("Account_Note")->writeNote($account, "EMAIL/ADDED", 707070, $data, Enum_Account_Note_Type::SYSTEM);
        
        // Set primary?
        if($primary){
            $newEmail->set_primary($email, $account);
        }
        
        return true;
    }
    
    /**
     * Check whether the current email has been assigned to an SSO system.
     * 
     * @param string $system The system to check for an assignment for.
     * @param integer $id The account ID to use.
     * @param boolean $returnEmail If set to TRUE the email will be returned, instead of true/false.
     * @return boolean TRUE if this system has an email assignment. FALSE otherwise.
     */
    public function assigned_to_sso($system, $id=null, $returnEmail=false){
        // Use the ID?
        if($id != null){
            $this->where("account_id", "=", $id);
        }
        
        // Let's loop through ALL emails
        foreach($this->where("deleted", "IS", NULL)->find_all() as $email){
            $sso = $email->sso_email->find();
            if($sso->loaded() && $sso->sso_system == $system){
                if($returnEmail && $sso->email->loaded()){
                    return $sso->email->email;
                } else {
                    return true;
                }
            }
        }

        // If we still don't have a return by now, let's just get the primary!
        // Use the ID?
        if($id != null){
            $this->where("account_id", "=", $id);
        }
        return $this->get_active_primary(false)->email;
    }
    
    // Check email is unique in the database.
    public function email_check_unique($email) {
        return false;
        return !(bool) ORM::factory("Account_Email")
                        ->where("id", "!=", $this->id)
                        ->where("email", "=", $email)
                        ->where("deleted", "=", NULL)
                        ->count_all() > 0;
    }

    // Gotta love __toString!
    public function __toString() {
        return ($this->email ? $this->email : "");
    }
    
    /**
     * Set the primary email for the given account!
     */
    public function set_primary(){
        if(!$this->loaded()){
            return false;
        }
        
        // Demote the old primary, if set.
        $oldPrimary = $this->get_active_primary();
        if($oldPrimary->loaded()){
            $oldPrimary->primary = 0;
            $oldPrimary->save();
            
            // Log it!
            $data = array($oldPrimary->email);
            ORM::factory("Account_Note")->writeNote($this->account, "EMAIL/PRIMARY_DEMOTED", 707070, $data, Enum_Account_Note_Type::SYSTEM);
        }
        
        // It exists, update it to set primary = 1
        $this->primary = 1;
        $this->save();

        // Log it!
        $data = array($this->email);
        ORM::factory("Account_Note")->writeNote($this->account, "EMAIL/PRIMARY_PROMOTED", 707070, $data, Enum_Account_Note_Type::SYSTEM);
    }
    
    // Pre-get_active_*
    private function helper_pre_get_active(){
        return $this->where("deleted", "IS", NULL);
    }
    
    /**
     * Get the current primary email for this account
     * 
     * @param boolean $idOnly If set to TRUE, the id will be returned and not the entire object.
     * @return int|Model_Account_Email
     */
    public function get_active_primary($idOnly=false){
        // Limit to primary.
        $finder = $this->account->emails->helper_pre_get_active()->where("primary", "=", "1")->find();
        
        // Found one?
        if($finder->loaded()){
            if($idOnly){
                return $finder->id;
            } else {
                return $finder;
            }
        }
        
        // Found nothing! :-(
        if($idOnly){
            return 0;
        } else {
            return ORM::factory("Account_Email");
        }
    }
    
    // Get the current secondary emails for this account
    public function get_active_secondary(){      
        return $this->helper_pre_get_active()->where("primary", "=", "0")->find_all();
    }

}

?>
