<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Account_Default extends Controller_Membership_Master {
    protected $_permissions = array(
        "_" => array('*'),
    );
    
    public function before(){
        return;
    }
    
    public function after(){
        return;
    }
    
    public function getDefaultAction(){
        return "account";
    }
    
    public function action_def(){
        $this->redirect("account/login/");
    }
}