<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Global extends Controller_Master {
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
    
    public function action_navbar(){
        $this->setTemplate("Navbar");
    }
    
    public function action_menu(){
        $this->setTemplate("Menu");
    }
    
    public function action_breadcrumbs(){
        $this->setTemplate("Breadcrumbs");
    }
    
    public function action_footer(){
        $this->setTemplate("Footer");
    }
    
    public function action_sidebar($sidebar=NULL){
        // Has a sidebar been requested?
        if($sidebar == NULL){
            if($this->request->param("area") !== NULL){
                // Convert to filename.
                $sidebar = str_replace("_", "/", $this->request->param("area") . "Sidebar");

                // Check it exists.
                if(Kohana::find_file("views", $this->_area . "/" . $sidebar)){
                    $this->action_sidebar($sidebar);
                    return;
                }
            }
            $this->action_sidebar("Sidebar");
            return;
        }
        
        // Get the content for the sidebar
        $content = ORM::factory("Content")
                      ->where("type", "=", "category")
                      ->order_by("sort_order", "ASC")
                      ->find_all();
        
        // Now get this sidebar
        $this->setTemplate($sidebar);
        $this->_data["content"] = $content;
    }

    protected function getDefaultAction() {
        return "navbar";
    }

    protected function hasPermission() {
        return true;
    }
}