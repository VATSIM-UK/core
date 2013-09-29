<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Sso_Error extends Controller_Sso_Master {
    /**
     * Index for all errors.
     */
    public function action_display(){
        $this->_data["error_type"] = Arr::get($_GET, "e", "UNKNOWN");
        $this->_data["error_route"] = Arr::get($_GET, "r", "UNKNOWN");
    }
}