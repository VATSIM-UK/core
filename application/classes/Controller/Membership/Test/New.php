<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Membership_Test_New extends Controller_Membership_Master {
    protected $_permissions = array(
        "_" => array('*'),
    );
    
    public function getDefaultAction(){
        return "user";
    }

    public function before() {
        parent::before();

        // Add to the breadcrumb
        $this->addBreadcrumb("Manage", "manage");
    }

    public function after() {
        parent::after();
    }

    public function action_user (){
         $this->setTitle("Find User");
         $this->setTemplate("Manage/User");
         $this->addBreadcrumb("Find User", "user");
    }
    
}