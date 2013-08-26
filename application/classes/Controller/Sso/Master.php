<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Sso_Auth extends Controller_Master {
    protected $_templateDir = "Standalone"; // Override parent settings.
    protected $_current_token = null;
    protected $_current_account = null;
    protected $_actual_account = null;

    public function __construct($request, $response){
        parent::__construct($request, $response);
        
        // Now let's load the current token!
        $this->_current_token = ORM::factory("Sso_Token")->get_current_token();
    }
}