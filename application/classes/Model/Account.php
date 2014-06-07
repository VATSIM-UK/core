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
        
        // Need to create account?
        if(!$this->loaded()){
            $this->id = $id;
            $this->save();
        }
        
        if($this->check_requires_cert_update()){
            $this->action_update_from_remote();
        }
        
        parent::__construct($id);
    }
}

?>