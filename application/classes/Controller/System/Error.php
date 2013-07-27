<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_System_Error extends Controller_Account_Master {

    protected $_permissions = array(
        "_" => array('*'),
    );

    public function getDefaultAction() {
        return "403";
    }

    public function before() {
        parent::before();
    }

    public function after() {
        parent::after();
    }
    
    public function action_403() {
        $this->setTemplate("Error/403");
    }

}