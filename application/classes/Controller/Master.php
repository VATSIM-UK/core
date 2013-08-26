<?php

defined('SYSPATH') or die('No direct script access.');

abstract class Controller_Master extends Controller_Template {

    protected $_config = array();
    protected $_permissions = NULL;
    protected $view = NULL;
    protected $_wrapper = TRUE;
    protected $_templateDir = "V3";
    protected $_area = NULL;
    protected $_controller = null;
    protected $_action = null;
    protected $_data = array();
    protected $_breadcrumbs = array();
    protected $_title = NULL;
    protected $_messages = array();

    public function hasPermission() {
        return true;
    }

    public function __construct($request, $response) {
        parent::__construct($request, $response);

        // Disable auto-rendering!
        $this->auto_render = FALSE;

        // Default the standard _data entries
        $this->_data = array(
            "title" => NULL,
            "breadcrumb" => array(),
        );
        // TODO: Get name from database.
        $this->addBreadcrumb("VATSIM-UK", "");

        // Determine the "area" (i.e the base folder)
        $this->_area = explode("_", get_class($this));
        $this->_area = (count($this->_area) > 1) ? $this->_area[1] : "Site";
        $this->_controller = ucfirst($this->request->controller());
        $this->_action = $this->request->action();

        // Load the settings from the database.
        $_tmp = ORM::factory("Setting")->find_all();
        $this->_data["_config"] = array();
        foreach ($_tmp as $v) {
            $key = $v->area . "." . $v->section;
            $key.= $v->key ? "." . $v->key : "";
            $this->_data["_config"][$key] = $v->value;
        }
        $this->_config = ORM::factory("Setting");

        // Now, let's get the membership details
        $this->_account = ORM::factory("Account", Session::instance(ORM::factory("Setting")->getValue("system.session.type"))->get(ORM::factory("Setting")->getValue("session.account.key")));
    }

    public function before() {
        // Does this user have permission to access this action?
        if (!$this->hasPermission()) {
            die("NO PERMISSION!");
            return;
        }

        // Add to the breadcrumb
        $this->addBreadcrumb($this->_area, "/");
        $this->addBreadcrumb($this->_controller, $this->_controller . "/");
        $this->addBreadcrumb($this->_action, $this->_controller . "/" . $this->_action . "/");
    }

    public function after() {
        // Template setup!
        $this->setTemplate(null);
        $this->setTitle($this->_controller . " " . $this->_action);

        // Now set all variables to view and/or template.
        foreach ($this->_data as $k => $v) {
            $this->_view->set($k, $v);
            if ($this->_wrapper === TRUE) {
                $this->template->set($k, $v);
            }
        }

        // Set the view template variables
        if ($this->request->is_initial()) {
            $this->_view->bind_global("_area", $this->_area);
            $this->_view->bind_global("_controller", $this->_controller);
            $this->_view->bind_global("_action", $this->_action);
            $this->_view->bind_global("_title", $this->_title);
            $this->_view->bind_global("_breadcrumbs", $this->_breadcrumbs);
            $this->_view->bind_global("_messages", $this->_messages);
            $this->_view->bind_global("_account", $this->_account);
        }

        // If there's a wrapper, set the different elements to the template too!
        if ($this->_wrapper === TRUE) {
            $this->template->set("_area", $this->_area);
            $this->template->set("_controller", $this->_controller);
            $this->template->set("_action", $this->_action);
            $this->_view->set("_content", $this->template->render());
        }

        // Display the template.
        $this->response->body($this->_view->render());
    }

    public function setTemplate($template = null) {
        // Template name
        if ($template == null) {
            $_a = str_replace("_", "/", $this->_action);
            $actions = "";
            foreach(explode("/", $_a) as $a){
                $actions.= ucfirst($a)."/";
            }
            $actions = rtrim($actions, "/");
            $template = $this->_area . "/" . $this->_controller . "/" . $actions;
        }
        
        // Add the template directory
        $template = $this->_templateDir . "/" . $template;

        // Now create and store the template. If there's no wrapper, it's the main view!
        if ($this->_wrapper === FALSE) {
            $this->_view = View::factory($template);
        } else {
            $this->_view = View::factory($this->_templateDir . "/Global/Wrapper");
            $this->template = View::factory($template);
        }
    }

    public function addBreadcrumb($name, $uri) {
        $this->_breadcrumbs[] = array("name" => $name, "url" => $uri);
    }

    public function setTitle($title) {
        $this->_title = $title;
    }

    public function setMessage($title, $message, $type) {
        if (!is_array($this->_messages)) {
            $this->_messages = array();
        }
        if (!isset($this->_messages[$type]) || !is_array($this->_messages[$type])) {
            $this->_messages[$type] = array();
        }

        $m = new stdClass();
        $m->title = $title;
        $m->message = $message;
        $this->_messages[$type][] = $m;
    }

}