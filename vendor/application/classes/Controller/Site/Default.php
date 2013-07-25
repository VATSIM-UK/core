<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Account_Default extends Controller_Site_Master {
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
        return "homepage";
    }
    
    public function action_def(){
        $this->redirect("homepage");
    }
}