<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Account_Master extends Model_Master {
    /**
     * Override the save method so we can log all changes to a member's account.
     * 
     * @return void
     * @override
     */
    public function save(\Validation $validation = NULL) {
        parent::save($validation);
        
        // Let's log the update - provided it's not a note, or similar....
        if(!preg_match("/^Model_Account_Note/i", get_class($this))){
            if(count($this->changed()) > 0){
                print "<pre>"; print_r($this->changed()); exit();
            }
        } else {
            die(">>>>".get_class($this));
        }
    }
}

?>