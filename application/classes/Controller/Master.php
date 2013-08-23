<?php

defined('SYSPATH') or die('No direct script access.');

abstract class Controller_Master extends Controller_Template {
    protected $_permissions = NULL;
    protected $view = NULL;
    protected $_wrapper = TRUE;
    protected $_templateDir = "V3";
    protected $_area = NULL;
    protected $_data = array();
    protected $_breadcrumbs = array();
    protected $_title = NULL;
    
    abstract protected function getDefaultAction();
    
    public function action_index($return=false){
        if($return === true){
            return (strcasecmp($this->request->action, "index") ? "action_".$this->getDefaultAction() : $this->request->action);
        } else {
            $this->{"action_".$this->getDefaultAction()}();
        }
    }

    public function __construct($request, $response) {
        parent::__construct($request, $response);

        // Disable auto-rendering!
        $this->auto_render = FALSE;

        // Default the standard _data entries
        $this->_data = array(
            "title" => NULL, "styles" => array(), "scripts" => array(),
            "breadcrumb" => array(),
        );
        // TODO: Get name from database.
        $this->addBreadcrumb("VATSIM-UK", "");

        // Determine the "area" (i.e the base folder)
        $this->_area = explode("_", get_class($this));
        $this->_area = (count($this->_area) > 1) ? $this->_area[1] : "Site";

        // Load the general config file as data.
        $_tmp = Kohana::$config->load("general");
        foreach ($_tmp as $k => $v) {
            $this->_data["config_" . $k] = $v;
        }
        
        // Now, let's get the membership details
        $this->_account = ORM::factory("Account", Session::instance("native")->get($this->_data["config_session_name"]));
    }
    

    
    protected function hasPermission(){
        // Get the account type
        /*$type = is_object($this->_account) ? $this->_account->type : Enum_Account_Types::GUEST;
        
        // If the permissions variable is NULL or an empty array, just... no.
        if($this->_permissions == NULL || (is_array($this->_permissions) && count($this->_permissions) < 1)){
            return false;
        }
        
        // Is there a key for this action?
        if(!array_key_exists($this->request->action(), $this->_permissions) || !is_array($this->_permissions[$this->request->action()])){
            // Since there isn't, check the global settings.
            if(array_key_exists("_", $this->_permissions) && is_array($this->_permissions["_"])){
                // Do the global settings permit anyone (i.e wildcard?)
                if(in_array("*", $this->_permissions["_"])){
                    return true;
                } else {
                    return in_array($type, $this->_permissions["_"]);
                }
            } else {
                // No, there's no  permissions at all it seems.
                return false;
            }
        }
        
        // Since there's a specific key, is it an array?
        return in_array($type, $this->_permissions[$this->request->action()]);*/
        return true;
    }
    
    public function before() {
        // Does this user have permission to access this action?
        if(!$this->hasPermission()){
            $this->redirect($this->getDefaultAction());
            return;
        }
        
        // Add to the breadcrumb
        $this->addBreadcrumb($this->_area, URL::site((strcasecmp($this->_area, "site") == 0) ? "" : $this->_area));
    }

    public function after() {
        if(is_object($this->view)){
            // Set the global variables - only if it's the initial request though!
            //if($this->request->is_initial() || !$this->_wrapper){
                $this->view->bind_global("_title", $this->_title);
                $this->view->bind_global("_breadcrumbs", $this->_breadcrumbs);
                $this->view->bind_global("_ajax_functions", $this->_ajaxFunctions);
                $this->view->bind_global("request", $this->request);
                $this->view->bind_global("_account", $this->_account);
                $this->view->set_global("_errors", Session::instance("native")->get("errors", array()));
                Session::instance("native")->delete("errors");
            //}

            // Now set all variables to view and/or template.
            foreach ($this->_data as $k => $v) {
                $this->view->set($k, $v);
                if ($this->_wrapper === TRUE) {
                    $this->template->set($k, $v);
                }
            }
            
            // Set the view template variables.
            $this->view->set("_area", $this->_area);
            $this->view->set("_controller", $this->request->controller());
//            $this->view->set("_action", $this->action_index(true));

            // If there's a wrapper, set the different elements to the template too!
            if($this->_wrapper === TRUE && is_object($this->template)){
                $this->template->set("_area", $this->_area);
                $this->view->set("_content", $this->template->render());
            }
            
            // Display the template.
            $this->response->body($this->view->render());
        }
    }

    public function setTemplate($template) {
        // Now create and store the template. If there's no wrapper, it's the main view!
        if (!$this->_wrapper) {
            $this->view = View::factory($this->_templateDir . "/" . $this->_area . "/" . $template);
        } else {
            $this->view = View::factory($this->_templateDir . "/Global/Wrapper");
            $this->template = View::factory($this->_templateDir . "/" . $this->_area . "/" . $template);
        }
    }

    public function addBreadcrumb($name, $uri) {
        $this->_breadcrumbs[] = array("name" => $name, "url" => $uri);
    }
    
    public function setTitle($title){
        $this->_title = $title;
    }
    
    public function setErrors($errors){
        $this->_data["_errors"] = array();
        foreach($errors as $error){
            if(is_array($error)){ $error = $error[key($error)]; }
            $error = explode(" ", $error);
            $this->_data["_errors"][] = $error[0];
        }
    }
}