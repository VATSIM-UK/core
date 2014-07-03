<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Error extends Controller_Master {
    protected $_wrapper = FALSE;
    protected $_templateDir = "Standalone";
    
    /**
     * Display the upgrade error.
     */
    public function action_upgrade() {
        $this->_wrapper = FALSE;
        $this->setTitle("Upgrade in Progress");
    }
    
    /**
     * Display a generic error.
     */
    public function action_generic(){
        $this->setTitle("Oops - it seems there's a problem...");
    }
}
