<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Training_Status extends Controller_Training_Master {
     protected $_permissions = array(
          "_" => array('*'),
     );

     public function getDefaultAction(){
          return "account";
     }

     public function before() {
          parent::before();
     }

     public function after() {
          parent::after();
     }

     public function action_account(){
          $this->setTemplate("Status/Test");
          $this->setTitle("Test Page");
     }
}    

?>
