<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Partial extends Controller_Master {
    protected $_wrapper = FALSE;
    protected $_permissions = array(
        "_" => array('*'),
    );
    
    public function before(){
        parent::before();
    }
    
    public function after(){
        parent::after();
    }
    
    public function action_featured(){
        $this->setTemplate("Featured");
    }

    protected function getDefaultAction() {
        return "featured";
    }

    protected function hasPermission() {
        return true;
    }
}