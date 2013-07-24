<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Sso_Default extends Controller_Sso_Master {
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
        return "def";
    }
    
    public function action_def(){
        $this->redirect("sso/auth/");
    }
}