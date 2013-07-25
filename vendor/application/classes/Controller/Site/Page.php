<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Site_Page extends Controller_Site_Master {
    protected $_permissions = array(
        "_" => array('*'),
    );
    
    public function before(){
        parent::before();
    }
    
    public function after(){
        parent::after();
    }
    
    public function action_default(){
        $this->redirect("homepage");
    }
    
    public function action_homepage(){
        /** TEMPLATE SETTINGS **/
        $this->setTemplate("Page/Homepage");
        $this->addBreadcrumb("Homepage", "homepage");
        $this->setTitle("Welcome to ".$this->_data["config_site_title"]);
    }
    
    public function action_display(){
        // Get the page stub
        $page = $this->request->param("page");
        $content = ORM::factory("Content")
                      ->where("name_url", "=", $page)
                      ->where("type", "=", "page")
                      ->find();
        
        // If there isn't one, display the homepage!
        if(!$content->loaded()){
            $this->action_homepage();
            return;
        }
        
        /** TEMPLATE SETTINGS **/
        $this->setTemplate("Page/Display");
        $this->addBreadcrumb($content->name, $content->name_url);
        $this->setTitle($content->name);
        $this->_data["content"] = $content;
    }
}