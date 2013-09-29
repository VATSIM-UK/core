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
        if(!$this->loaded() || $this->check_requires_cert_update()){
            Helper_Account::update_using_remote($id);
        }
    }
}

?>