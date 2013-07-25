<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Training_Theory extends Controller_Training_Master {
    protected $_permissions = array(
        "_" => array('*'),
    );
    
    public function getDefaultAction(){
          return "view";
     }

     public function before() {
          parent::before();
          $this->addBreadcrumb('Theory', 'training/theory');
     }

     public function after() {
          parent::after();
     }

     public function action_view(){
          $this->setTemplate("Status/Test");
          $this->setTitle("Test Page");
     }
     
     public function action_take(){
          
          // the theory test requested
          $theory = ORM::factory("Training_Theory", intval($this->request->query('test')));
          // no theory test found, redirect to theory home
          if (!$theory->loaded()){
               $this->redirect('training/theory');
          }
          
          $user = ORM::factory("Account", 1010573);
          
          // is the user allowed to take this theory test?
          if (!$theory->mayTake($user)){
               $this->redirect('training/theory');
          }
          
          
          
     }
     
     
}

?>