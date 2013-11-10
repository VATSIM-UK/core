<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Account_Security_Reset extends Model_Master {

    protected $_db_group = 'mship';
    protected $_table_name = 'account_security_reset';
    protected $_table_columns = array(
        'id' => array('data_type' => 'int'),
        'account_id' => array('data_type' => 'int'),
        'code' => array('data_type' => 'varchar'),
        'created' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'expires' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'used' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'enabled' => array('data_type' => 'boolean'),
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
    protected $_has_one = array(
    );
    
    // Validation rules
    public function rules(){
        return array(
        );
    }
    
    // Data filters
    public function filters(){
        return array();
    }
    
    /**
     * Generate a new password reset code for the specified individual.
     * 
     * Will also queue an email for the individual.
     * 
     * @param int $account_id The account ID to generate a password reset link for.
     * @return boolean True on success, false otherwise.
     */
    public function action_generate($account_id){
        $account = ORM::factory("Account_Main", $account_id);
        if(!$account->loaded()){
            return;
        }
        
        // Let's disable all outstanding ones!
        $old = $account->security_resets->where("enabled", "=", 1)->find_all();
        foreach($old as $o){
            $o->enabled = 0;
            $o->save();
        }
        
        // Let's create!
        $this->account_id = $account_id;
        $this->code = uniqid(Text::random("distinct", 7)."-", true);
        $this->created = gmdate("Y-m-d H:i:s");
        $this->expires = gmdate("Y-m-d H:i:s", strtotime("+".ORM::factory("Setting")->getValue("auth.sso.security.reset_time")." seconds"));
        $this->enabled = 1;
        $this->save();
        
        // Queue an email!
        ORM::factory("Postmaster_Queue")->action_add("SSO_SLS_RESET", $account_id, null, 
                    array(
                        "timestamp" => $this->created,
                        "ip_address" => Arr::get($_SERVER, "REMOTE_ADDR", "Unavailable"),
                        "reset_code" => $this->code,
                        "hash" => sha1($this->code.$this->id),
                    ));
        
        return true;
    }
    
    /**
     * Validate the clicked link and generate a new password.
     * 
     * @param string $code The given code, used to find the reset.
     * @param string $hash The hash, used to validate the click.
     * @return boolean True if successful, false otherwise.
     */
    public function action_clickLink($code, $hash){
        // Try and find the right reset....
        $reset = ORM::factory("Account_Security_Reset")
                    ->where("code", "=", $code)
                    ->where("expires", "<=", gmdate("Y-m-d H:i:s"))
                    ->where("enabled", "=", 1)
                    ->find();
        
        if(!$reset->loaded()){
            return false;
        }
        
        // So it's valid... or is it?!
        if(sha1($code.$reset->id) != $hash){
            return false;
        }
        
        // Expire this link!
        $reset->enabled = 0;
        $reset->used = gmdate("Y-m-d H:i:s");
        $reset->save();
        
        // Let's generate....
        $random = Text::random("distinct", 12);
        
        // Let's update....
        $reset->account->security->value = $random;
        $reset->account->security->created = gmdate("Y-m-d H:i:s");
        $reset->account->security->expires = gmdate("Y-m-d H:i:s");
        $reset->account->security->save();
        
        // Now email them!
        ORM::factory("Postmaster_Queue")->action_add("SSO_SLS_FORGOT", $reset->account->id, null, array("temp_password" => $random));
        return true;
    }
}

?>