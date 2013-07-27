<?php

defined('SYSPATH') or die('No direct script access.');

abstract class Controller_Training_Master extends Controller_Master {
    // User data.
    protected $_account = NULL;
    
    public function before(){
        parent::before();
    }
    
    public function after(){
        parent::after();
    }
    
    protected function getDefaultAction() {
        return "account";
    }
}