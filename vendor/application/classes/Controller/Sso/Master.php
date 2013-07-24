<?php

defined('SYSPATH') or die('No direct script access.');

abstract class Controller_Sso_Master extends Controller_Master {
    // User data.
    protected $_account = NULL;
    protected $_wrapper = FALSE;
    protected $_templateDir = "Standalone";
    
    public function before(){
        parent::before();
    }
    
    public function after(){
        parent::after();
    }
    
    protected function getDefaultAction() {
        return "login";
    }
}