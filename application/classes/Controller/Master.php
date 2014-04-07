<?php

defined('SYSPATH') or die('No direct script access.');

abstract class Controller_Master extends Controller_Template {

    protected $_config = array();
    protected $_permissions = NULL;
    protected $_view = NULL;
    protected $_view_fn = NULL;
    protected $_template = NULL;
    protected $_template_fn = NULL;
    protected $_wrapper = TRUE;
    protected $_templateDir = "V3";
    protected $_templateOverride = false;
    protected $_area = NULL;
    protected $_controller = null;
    protected $_action = null;
    protected $_data = array();
    protected $_breadcrumbs = array();
    protected $_title = NULL;
    protected $_messages = array();
    protected $_current_token = null;
    protected $_current_account = null;
    protected $_actual_account = null;

    protected function loadAccount() {
        $this->_current_account = ORM::factory("Account")->get_current_account();
    }

    protected function loadToken() {
        $this->_current_token = ORM::factory("Sso_Token")->get_current_token();
    }

    public function hasPermission() {
        return true;
    }

    public function session() {
        return Session::instance(ORM::factory("Setting")->getValue("system.session.type"));
    }

    public function __construct($request, $response) {
        parent::__construct($request, $response);

        // Disable auto-rendering!
        $this->auto_render = FALSE;

        // Default the standard _data entries
        $this->_data = array(
            "title" => NULL,
            "breadcrumb" => array(),
            "scripts" => array(),
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
        $this->_account = ORM::factory("Account_Main", $this->session()->get(ORM::factory("Setting")->getValue("auth.account.session.key")));
        //echo "<pre>" . print_r($this->_account, true); exit();
        $this->loadAccount();
        $this->loadToken();

        // Has the member changed the template they're using?
        if ($this->_templateDir != "Standalone" && !$this->_templateOverride && $this->_account->template != "") {
            if (file_exists(APPPATH . "views/" . $this->_account->template)) {
                $this->_templateDir = $this->_account->template;
            }
        }
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

        $this->setTitle(ucfirst($this->_action));

        $this->loadAccount();
        $this->loadToken();
    }

    public function after() {
        // Template setup!
        if ($this->_template == "template" OR ($this->_view == NULL)) {
            $this->setTemplate(null);
        }

        // What about any Javascript files?
        // Let's check for any JS specific to the VIEW
        $_v_a = explode("/", $this->_view_fn);
        $_cur = "";
        foreach ($_v_a as $_) {
            $_cur.= $_;
            if (file_exists(DOCROOT . "media" . DIRECTORY_SEPARATOR . "scripts" . DIRECTORY_SEPARATOR . $_cur . ".js")) {
                $this->_data["scripts"][] = "media/scripts/" . $_cur . ".js";
            }
            $_cur.= "/";
            if (file_exists(DOCROOT . "media" . DIRECTORY_SEPARATOR . "scripts" . DIRECTORY_SEPARATOR . $_cur . "global.js")) {
                $this->_data["scripts"][] = "media/scripts/" . $_cur . "global.js";
            }
        }

        // Cheeck for JS specific to the TEMPLATE.
        if ($this->_template != NULL) {
            $_t_a = explode("/", $this->_template_fn);
            $_cur = "";
            foreach ($_t_a as $_) {
                $_cur.= $_;
                if (file_exists(DOCROOT . "media" . DIRECTORY_SEPARATOR . "scripts" . DIRECTORY_SEPARATOR . $_cur . ".js")) {
                    $this->_data["scripts"][] = "media/scripts/" . $_cur . ".js";
                }
                $_cur.= "/";
                if (file_exists(DOCROOT . "media" . DIRECTORY_SEPARATOR . "scripts" . DIRECTORY_SEPARATOR . $_cur . "global.js")) {
                    $this->_data["scripts"][] = "media/scripts/" . $_cur . "global.js";
                }
            }

            // Trim any duplicates!
            $this->_data["scripts"] = array_unique($this->_data["scripts"]);
        }

        // Now set all variables to view and/or template.
        foreach ($this->_data as $k => $v) {
            $this->_view->set($k, $v);
            if ($this->_wrapper === TRUE) {
                $this->_template->set($k, $v);
            }
        }

        // Load the flash messages
        $this->_messages = unserialize($this->session()->get_once("flash_messages", serialize(array())));

        // Set the view template variables
        if ($this->request->is_initial()) {
            $this->_view->bind_global("_area", $this->_area);
            $this->_view->bind_global("_controller", $this->_controller);
            $this->_view->bind_global("_action", $this->_action);
            $this->_view->bind_global("_title", $this->_title);
            $this->_view->bind_global("_breadcrumbs", $this->_breadcrumbs);
            $this->_view->bind_global("_messages", $this->_messages);
            $this->_view->bind_global("_account", $this->_current_account);
            $this->_view->bind_global("_request", $this->request);
        }

        // If there's a wrapper, set the different elements to the template too!
        if ($this->_wrapper === TRUE) {
            $this->_template->set("_area", $this->_area);
            $this->_template->set("_controller", $this->_controller);
            $this->_template->set("_action", $this->_action);
            $this->_view->set("_content", $this->_template->render());
        }

        // Display the template.
        $this->response->body($this->_view->render());
    }

    public function setTemplate($template = null) {
        // Template name
        if ($template == null) {
            // Sort the final actions out.
            $_a = str_replace("_", "/", $this->_action);
            $actions = "";
            foreach (explode("/", $_a) as $a) {
                $actions.= ucfirst($a) . "/";
            }
            $actions = rtrim($actions, "/");
            $template = $this->_area . "/" . str_replace("_", "/", $this->_controller) . "/" . $actions;
        } else {
            $_t = explode("/", $template);
            $template = "";
            foreach($_t as $_){
                $template.= ucfirst($_)."/";
            }
            $template = rtrim($template, "/");
        }

        // Add the template directory
        $template = $this->_templateDir . "/" . $template;

        // Now create and store the template. If there's no wrapper, it's the main view!
        if ($this->_wrapper === FALSE) {
            $this->_view_fn = $template;
            $this->_view = View::factory($this->_view_fn);
        } else {
            $this->_view_fn = $this->_templateDir . "/Global/Wrapper";
            $this->_view = View::factory($this->_view_fn);
            $this->_template_fn = $template;
            $this->_template = View::factory($this->_template_fn);
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

        // Do we need to append to a previous error? (same title)?
        $exists = false;
        foreach ($this->_messages[$type] as $key => $error) {
            if ($error->title == $title) {
                $exists = true;
                $error->message .= "<br />" . $message;
                $this->_messages[$type][$key] = $error;
            }
        }

        if (!$exists) {
            $m = new stdClass();
            $m->title = $title;
            $m->message = $message;
            $this->_messages[$type][] = $m;
        }

        // Now let's make the messages a flash storage!
        $this->session()->set("flash_messages", serialize($this->_messages));
    }

}
