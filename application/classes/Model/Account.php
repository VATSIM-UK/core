<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Account extends Model_Account_Main {
    /**
     * Override the constructor so that if an account doesn't exist, it's created from the VATSIM data feed.
     * 
     * @return Model_Account_Main The currently loaded model.
     */
    public function __construct($id = NULL){
        parent::__construct($id);
        
        if($id == NULL){
            return $this;
        }
        
        // Cert update?
        $newMember = false;
        if(!$this->loaded() || $this->check_requires_cert_update()){
            if(!$this->loaded()){
                $newMember = true;
            } else {
                $newMember = false;
            }
            Helper_Account::update_using_remote($id);
        }
        parent::__construct($id);
        if($newMember){
            ORM::factory("Postmaster_Queue")->action_add("SSO_CREATED", $this->id, null, 
                    array(
                        "primary_email" => $this->emails->get_active_primary()->email,
                        "account_state" => $this->getState(),
                    ));
        }
    }
}

?>