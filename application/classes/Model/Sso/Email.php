<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Sso_Email extends Model_Master {

    protected $_db_group = 'sso';
    protected $_table_name = 'email';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'account_email_id' => array('data_type' => 'bigint'),
        'sso_system' => array("data_type" => "string"),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
        'email' => array(
            'model' => 'Account_Email',
            'foreign_key' => 'account_email_id',
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
     * Assign an email to an account.
     * 
     * @param int $account_email_id The account email ID
     * @param string $system The SSO system to assign the email to.
     * @return void
     */
    public function assign_email($account_email_id, $system){
        // Is there an email for this already?
        if(ORM::factory("Sso_Email")->where("account_email_id", "=", $account_email_id)->where("sso_system", "=", $system)->find()->loaded()){
            return;
        }
        
        // Assign the new email
        $newEmail = ORM::factory("Sso_Email");
        $newEmail->account_email_id = $account_email_id;
        $newEmail->sso_system = $system;
        $newEmail->save();
    }
}

?>