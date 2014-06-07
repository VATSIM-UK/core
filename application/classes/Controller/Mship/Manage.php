<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Mship_Manage extends Controller_Mship_Master {

    public function before() {
        parent::before();
    }

    public function action_landing(){
        // Send them away if they're logged in!
        if (is_object($this->_current_account) && $this->_current_account->loaded()) {
            $this->redirect("/mship/manage/display");
            return true;
        }
    }
    
    public function action_display() {
        // If they're not logged in, we'll send them to a welcome page.
        if (!is_object($this->_current_account) OR !$this->_current_account->loaded()) {
            $this->redirect("/mship/manage/landing");
            return false;
        }

        // Set the account details
        $this->_data["_account"] = $this->_current_account;
    }
}
