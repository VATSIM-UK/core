<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Error extends Controller_Master {
    protected $_wrapper = FALSE;
    protected $_templateDir = "Standalone";
    
    /**
     * Display the upgrade error.
     */
    public function action_upgrade() {
        $this->setTitle("Upgrade in Progress");
    }
}
