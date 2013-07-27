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
          $key = $theory->mayTake($user);
          
          //are they currently taking this theory test?
          
          
          // is the user allowed to take this theory test?
          if (!$key){
               $this->redirect('training/theory');
          }
          
          
          
     }
     
     
}

?>