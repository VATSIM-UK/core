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
        
        if($id == NULL OR $id == "" OR $id == 0){
            return $this;
        }
        
        // Ignore system accounts
        if($this->isSystem()){
            return $this;
        }
        
        // If it's not loaded, set the ID
        if(!$this->loaded()){
            $this->id = $id;
        }
        
        // Do we need to run a data update?
        if($this->check_requires_cert_update()){
            $this->data_from_remote();
        }
        
        parent::__construct($id);
    }
}

?>